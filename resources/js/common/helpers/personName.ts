export function containsDigits(value: string): boolean {
  return /\d/.test(value);
}

export function sanitizePersonNameInput(value: string): string {
  return value
    .replace(/\d+/g, '')
    .split(' ')
    .map((segment) => {
      if (segment.length === 0) {
        return segment;
      }

      return segment
        .split('-')
        .map((part) => {
          if (part.length === 0) {
            return part;
          }

          return `${part.charAt(0).toUpperCase()}${part.slice(1).toLowerCase()}`;
        })
        .join('-');
    })
    .join(' ');
}
