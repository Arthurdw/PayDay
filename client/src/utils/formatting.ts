export const capitalize = (content: string): string =>
  content.charAt(0).toUpperCase() + content.slice(1);

export const formatNumberAsCurrency = (num: number): string => `$${num}`;
