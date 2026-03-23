import * as React from 'react';

export interface UserAddressAutocompleteValue {
  address: string;
  city: string;
  state: string;
  country: string;
  zip_code: string;
}

interface UseGoogleMapsAddressAutocompleteOptions {
  inputRef: React.RefObject<HTMLInputElement | null>;
  onAddressSelected: (value: UserAddressAutocompleteValue) => void;
  enabled?: boolean;
}

interface UseGoogleMapsAddressAutocompleteResult {
  isLoading: boolean;
  isReady: boolean;
  errorMessage: string | null;
}

type GoogleAddressComponent = {
  longText: string;
  shortText: string;
  types: string[];
};

type GooglePlace = {
  addressComponents?: GoogleAddressComponent[];
  formattedAddress?: string;
  fetchFields: (options: { fields: string[] }) => Promise<{ place: GooglePlace }>;
};

type GooglePlacePrediction = {
  toPlace: () => GooglePlace;
};

type GmpSelectEvent = Event & {
  placePrediction: GooglePlacePrediction;
};

type PlaceAutocompleteElementOptions = {
  includedRegionCodes?: string[];
  includedPrimaryTypes?: string[];
};

interface PlaceAutocompleteElementInstance {
  setAttribute(name: string, value: string): void;
  getAttribute(name: string): string | null;
  removeAttribute(name: string): void;
  remove(): void;
  addEventListener(
    type: 'gmp-select',
    listener: (event: GmpSelectEvent) => void,
  ): void;
  removeEventListener(
    type: 'gmp-select',
    listener: (event: GmpSelectEvent) => void,
  ): void;
}

type GooglePlacesLibrary = {
  PlaceAutocompleteElement: new (
    options?: PlaceAutocompleteElementOptions,
  ) => PlaceAutocompleteElementInstance;
};

declare global {
  interface Window {
    google?: {
      maps: {
        importLibrary: (library: string) => Promise<unknown>;
      };
    };
  }
}

const SCRIPT_ID = 'google-maps-places-script';
let googleMapsBootstrapPromise: Promise<void> | null = null;

function getGoogleMapsApiKey(): string {
  const publicKey = import.meta.env.PUBLIC_GOOGLE_MAPS_API_KEY;

  if (typeof publicKey === 'string' && publicKey.trim().length > 0) {
    return publicKey.trim();
  }

  const viteKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

  if (typeof viteKey === 'string' && viteKey.trim().length > 0) {
    return viteKey.trim();
  }

  return '';
}

function loadGoogleMapsBootstrap(apiKey: string): Promise<void> {
  if (window.google?.maps?.importLibrary) {
    return Promise.resolve();
  }

  if (googleMapsBootstrapPromise) {
    return googleMapsBootstrapPromise;
  }

  googleMapsBootstrapPromise = new Promise<void>((resolve, reject) => {
    const existingScript = document.getElementById(SCRIPT_ID);

    const handleLoad = (): void => {
      if (window.google?.maps?.importLibrary) {
        resolve();
        return;
      }
      reject(new Error('Google Maps bootstrap loaded but importLibrary is not available.'));
    };

    const handleError = (): void => {
      reject(new Error('Failed to load Google Maps bootstrap script.'));
    };

    if (existingScript instanceof HTMLScriptElement) {
      existingScript.addEventListener('load', handleLoad, { once: true });
      existingScript.addEventListener('error', handleError, { once: true });
      return;
    }

    const script = document.createElement('script');
    script.id = SCRIPT_ID;
    script.async = true;
    script.defer = true;
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&loading=async&libraries=places`;
    script.addEventListener('load', handleLoad, { once: true });
    script.addEventListener('error', handleError, { once: true });
    document.head.appendChild(script);
  }).catch((error: unknown) => {
    googleMapsBootstrapPromise = null;
    throw error;
  });

  return googleMapsBootstrapPromise;
}

async function loadPlacesLibrary(): Promise<GooglePlacesLibrary> {
  const lib = await window.google!.maps.importLibrary('places');
  return lib as GooglePlacesLibrary;
}

function buildAddressValue(
  components: GoogleAddressComponent[],
  formattedAddress: string | undefined,
): UserAddressAutocompleteValue {
  let streetNumber = '';
  let route = '';
  let city = '';
  let state = '';
  let country = '';
  let countryCode = '';
  let postalCode = '';
  let postalCodeSuffix = '';

  for (const component of components) {
    if (component.types.includes('street_number')) {
      streetNumber = component.longText;
      continue;
    }

    if (component.types.includes('route')) {
      route = component.shortText;
      continue;
    }

    if (component.types.includes('locality')) {
      city = component.longText;
      continue;
    }

    if (city.length === 0 && component.types.includes('postal_town')) {
      city = component.longText;
      continue;
    }

    if (city.length === 0 && component.types.includes('administrative_area_level_2')) {
      city = component.longText;
      continue;
    }

    if (component.types.includes('administrative_area_level_1')) {
      state = component.longText;
      continue;
    }

    if (component.types.includes('country')) {
      country = component.longText;
      countryCode = component.shortText;
      continue;
    }

    if (component.types.includes('postal_code')) {
      postalCode = component.longText;
      continue;
    }

    if (component.types.includes('postal_code_suffix')) {
      postalCodeSuffix = component.longText;
    }
  }

  const address = [streetNumber, route]
    .filter((segment) => segment.length > 0)
    .join(' ')
    .trim();

  const fallbackAddress =
    typeof formattedAddress === 'string'
      ? (formattedAddress.split(',')[0]?.trim() ?? '')
      : '';

  return {
    address: address.length > 0 ? address : fallbackAddress,
    city,
    state,
    country: countryCode === 'US' ? 'USA' : country,
    zip_code: postalCodeSuffix.length > 0 ? `${postalCode}-${postalCodeSuffix}` : postalCode,
  };
}

function injectAutocompleteStyles(): void {
  const styleId = 'gmp-autocomplete-override-styles';
  if (document.getElementById(styleId)) return;

  const style = document.createElement('style');
  style.id = styleId;
  style.textContent = `
    gmp-place-autocomplete {
      display: block;
      width: 100%;
    }
    gmp-place-autocomplete input,
    gmp-place-autocomplete::part(input) {
      width: 100%;
      font-family: var(--font-sans, inherit);
      font-size: 0.875rem;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      outline: none;
      transition: all 0.3s;
      background: var(--bg-card);
      border: 1px solid var(--border-default);
      box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
      color: var(--text-primary);
    }
    gmp-place-autocomplete input::placeholder,
    gmp-place-autocomplete::part(input)::placeholder {
      color: var(--text-disabled);
    }
    gmp-place-autocomplete input:hover,
    gmp-place-autocomplete::part(input):hover {
      border-color: var(--accent-primary);
    }
    gmp-place-autocomplete input:focus,
    gmp-place-autocomplete::part(input):focus {
      ring: 2px;
      ring-color: var(--accent-primary);
      border-color: var(--accent-primary);
    }
  `;
  document.head.appendChild(style);
}

export function useGoogleMapsAddressAutocomplete({
  inputRef,
  onAddressSelected,
  enabled = true,
}: UseGoogleMapsAddressAutocompleteOptions): UseGoogleMapsAddressAutocompleteResult {
  const [isLoading, setIsLoading] = React.useState<boolean>(false);
  const [isReady, setIsReady] = React.useState<boolean>(false);
  const [errorMessage, setErrorMessage] = React.useState<string | null>(null);

  const onAddressSelectedRef = React.useRef(onAddressSelected);
  onAddressSelectedRef.current = onAddressSelected;

  React.useEffect(() => {
    if (!enabled) {
      setIsLoading(false);
      setIsReady(false);
      setErrorMessage(null);
      return;
    }

    const input = inputRef.current;

    if (!input) {
      return;
    }

    const apiKey = getGoogleMapsApiKey();

    if (apiKey.length === 0) {
      setIsLoading(false);
      setIsReady(false);
      setErrorMessage('Google Maps API key is missing.');
      return;
    }

    let isCancelled = false;
    let autocompleteElement: PlaceAutocompleteElementInstance | null = null;

    setIsLoading(true);
    setErrorMessage(null);

    const handleSelect = (event: GmpSelectEvent): void => {
      if (isCancelled) return;

      const place = event.placePrediction.toPlace();

      void place
        .fetchFields({ fields: ['addressComponents', 'formattedAddress'] })
        .then(({ place: filledPlace }) => {
          if (isCancelled) return;

          const components = filledPlace.addressComponents ?? [];
          const value = buildAddressValue(components, filledPlace.formattedAddress);
          onAddressSelectedRef.current(value);

          if (input) {
            input.value = value.address;
          }
        })
        .catch(() => {
          if (!isCancelled) {
            setErrorMessage('Could not retrieve address details.');
          }
        });
    };

    void loadGoogleMapsBootstrap(apiKey)
      .then(loadPlacesLibrary)
      .then(({ PlaceAutocompleteElement }) => {
        if (isCancelled) return;

        injectAutocompleteStyles();

        autocompleteElement = new PlaceAutocompleteElement({
          includedRegionCodes: ['US'],
          includedPrimaryTypes: ['address'],
        });

        const placeholder = input.getAttribute('placeholder') ?? 'Start typing a USA address';
        autocompleteElement.setAttribute('placeholder', placeholder);

        const inputId = input.getAttribute('id');
        if (inputId) {
          autocompleteElement.setAttribute('id', `${inputId}-autocomplete`);
        }

        const parent = input.parentElement;

        if (parent) {
          const domEl = autocompleteElement as unknown as HTMLElement;
          domEl.style.cssText = 'display:block;width:100%;';
          input.style.display = 'none';
          parent.insertBefore(domEl, input.nextSibling);
        }

        autocompleteElement.addEventListener('gmp-select', handleSelect);

        setIsReady(true);
      })
      .catch(() => {
        if (!isCancelled) {
          setIsReady(false);
          setErrorMessage('Google Maps autocomplete could not be loaded.');
        }
      })
      .finally(() => {
        if (!isCancelled) {
          setIsLoading(false);
        }
      });

    return () => {
      isCancelled = true;

      if (autocompleteElement) {
        autocompleteElement.removeEventListener('gmp-select', handleSelect);
        (autocompleteElement as unknown as HTMLElement).remove();
        autocompleteElement = null;
      }

      if (input) {
        input.style.display = '';
      }
    };
  }, [enabled, inputRef]);

  return {
    isLoading,
    isReady,
    errorMessage,
  };
}
