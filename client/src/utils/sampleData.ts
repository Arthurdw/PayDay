import error from "@/assets/error.svg";

const defaultOverviewDataPart = {
  total: 0,
  lastMonth: 0,
};

export const defaultOverviewData = {
  income: defaultOverviewDataPart,
  spending: defaultOverviewDataPart,
  cashFlow: defaultOverviewDataPart,
  portfolio: defaultOverviewDataPart,
};

export const sourceFailed = {
  id: 0,
  name: "Failed",
  type: "Failed",
  imageUrl: error,
  description: "Failed to load source information.",
};
