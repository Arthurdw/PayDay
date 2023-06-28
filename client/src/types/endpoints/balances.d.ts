interface AddBalanceData {
  source_id: number;
  in: number;
  out: number;
  subscription: boolean;
}

interface GetBalanceResponse extends AddBalanceData {
  id: number;
  user_id: number;
  created_at: string;
  updated_at: string;
}

type AddBalanceResponse = GetBalanceResponse;
type GetAllBalancesResponse = Paginated<AddBalanceResponse>;
