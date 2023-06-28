export const statisticsResponseToChartStatistics = (
  data: GetStatisticsResponse
): ChartStatistics[] => {
  const dataFormatted = [] as ChartStatistics[];

  for (let i = 0; i < data.income.length; i++) {
    dataFormatted.push({
      index: i,
      Income: data.income[i],
      Spending: data.spending[i],
      CashFlow: data.cashFlow[i],
      Portfolio: data.portfolio[i],
    });
  }

  return dataFormatted;
};
