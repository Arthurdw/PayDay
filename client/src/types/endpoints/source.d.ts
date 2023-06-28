interface BaseSourceResponse {
  name: string;
  imageUrl: string;
  type: string;
}

interface GetSourceResponse extends BaseSourceResponse {
  id: number;
  description: string;
}

interface AddSourceData extends BaseSourceResponse {
  descriptionEnglish: string;
  descriptionDutch: string;
}

interface AddSourceResponse extends AddSourceData {
  id: number;
  created_at: string;
  updated_at: string;
}

type UpdateSourceData = Partial<{
  [K in keyof AddSourceData]: AddSourceData[K];
}>;

type UpdateSourceResponse = AddSourceResponse;
