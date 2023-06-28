export const calculateDiff = (num: number, lastMonth: number): number =>
  ((num - lastMonth) / lastMonth) * 100;
