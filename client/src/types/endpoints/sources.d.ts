type GetSourcesResponse = Paginated<GetSourceResponse>;

interface SourcePair {
  id: number;
  name: string;
}

type SourcesPairResponse = SourcePair[];
