interface OverviewItemData {
  total: number;
  lastMonth: number;
}

type GenericAnalyticsResponse<T> = {
  income: T;
  spending: T;
  cashFlow: T;
  portfolio: T;
};

type GetOverviewResponse = GenericAnalyticsResponse<OverviewItemData>;
type GetStatisticsResponse = GenericAnalyticsResponse<number[]>;
