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
  long_name: string;
  short_name: string;
  types: string[];
};

type GooglePlaceResult = {
  address_components?: GoogleAddressComponent[];
  formatted_address?: string;
};

type GoogleAutocompleteOptions = {
  componentRestrictions?: { country: string | string[] };
  fields?: string[];
  types?: string[];
};

type GoogleMapsEventListener = {
  remove: () => void;
};

type GoogleAutocompleteInstance = {
  addListener: (eventName: 'place_changed', handler: () => void) => GoogleMapsEventListener;
  getPlace: () => GooglePlaceResult;
};

type GooglePlacesNamespace = {
  Autocomplete: new (input: HTMLInputElement, options?: GoogleAutocompleteOptions) => GoogleAutocompleteInstance;
};

type GoogleMapsNamespace = {
  places: GooglePlacesNamespace;
};

type GoogleWindow = {
  maps: GoogleMapsNamespace;
};

declare global {
  interface Window {
    google?: GoogleWindow;
  }
}

const SCRIPT_ID = 'google-maps-places-script';
let googleMapsScriptPromise: Promise<GoogleWindow> | null = null;

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

function loadGoogleMapsScript(apiKey: string): Promise<GoogleWindow> {
  if (window.google) {
    return Promise.resolve(window.google);
  }

  if (googleMapsScriptPromise) {
    return googleMapsScriptPromise;
  }

  googleMapsScriptPromise = new Promise<GoogleWindow>((resolve, reject) => {
    const existingScript = document.getElementById(SCRIPT_ID);

    const handleLoad = (): void => {
      if (window.google) {
        resolve(window.google);
        return;
      }

      reject(new Error('Google Maps script loaded without google namespace.'));
    };

    const handleError = (): void => {
      reject(new Error('Failed to load Google Maps script.'));
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
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places&loading=async`;
    script.addEventListener('load', handleLoad, { once: true });
    script.addEventListener('error', handleError, { once: true });
    document.head.appendChild(script);
  }).catch((error: unknown) => {
    googleMapsScriptPromise = null;
    throw error;
  });

  return googleMapsScriptPromise;
}

function buildAddressValue(place: GooglePlaceResult): UserAddressAutocompleteValue {
  const components = place.address_components ?? [];

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
      streetNumber = component.long_name;
      continue;
    }

    if (component.types.includes('route')) {
      route = component.short_name;
      continue;
    }

    if (component.types.includes('locality')) {
      city = component.long_name;
      continue;
    }

    if (city.length === 0 && component.types.includes('postal_town')) {
      city = component.long_name;
      continue;
    }

    if (city.length === 0 && component.types.includes('administrative_area_level_2')) {
      city = component.long_name;
      continue;
    }

    if (component.types.includes('administrative_area_level_1')) {
      state = component.long_name;
      continue;
    }

    if (component.types.includes('country')) {
      country = component.long_name;
      countryCode = component.short_name;
      continue;
    }

    if (component.types.includes('postal_code')) {
      postalCode = component.long_name;
      continue;
    }

    if (component.types.includes('postal_code_suffix')) {
      postalCodeSuffix = component.long_name;
    }
  }

  const address = [streetNumber, route].filter((segment) => segment.length > 0).join(' ').trim();
  const fallbackAddress = typeof place.formatted_address === 'string'
    ? place.formatted_address.split(',')[0]?.trim() ?? ''
    : '';

  return {
    address: address.length > 0 ? address : fallbackAddress,
    city,
    state,
    country: countryCode === 'US' ? 'USA' : country,
    zip_code: postalCodeSuffix.length > 0 ? `${postalCode}-${postalCodeSuffix}` : postalCode,
  };
}

export function useGoogleMapsAddressAutocomplete({
  inputRef,
  onAddressSelected,
  enabled = true,
}: UseGoogleMapsAddressAutocompleteOptions): UseGoogleMapsAddressAutocompleteResult {
  const [isLoading, setIsLoading] = React.useState<boolean>(false);
  const [isReady, setIsReady] = React.useState<boolean>(false);
  const [errorMessage, setErrorMessage] = React.useState<string | null>(null);

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

    let listener: GoogleMapsEventListener | null = null;
    let isCancelled = false;

    setIsLoading(true);
    setErrorMessage(null);

    void loadGoogleMapsScript(apiKey)
      .then((google) => {
        if (isCancelled) {
          return;
        }

        const autocomplete = new google.maps.places.Autocomplete(input, {
          componentRestrictions: { country: 'us' },
          fields: ['address_components', 'formatted_address'],
          types: ['address'],
        });

        listener = autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          onAddressSelected(buildAddressValue(place));
        });

        setIsReady(true);
      })
      .catch(() => {
        if (isCancelled) {
          return;
        }

        setIsReady(false);
        setErrorMessage('Google Maps autocomplete could not be loaded.');
      })
      .finally(() => {
        if (!isCancelled) {
          setIsLoading(false);
        }
      });

    return () => {
      isCancelled = true;
      listener?.remove();
    };
  }, [enabled, inputRef, onAddressSelected]);

  return {
    isLoading,
    isReady,
    errorMessage,
  };
}
