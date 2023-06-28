interface Tag {
  type: TagType;
  content: string;
}

interface BarItem {
  icon: string;
  name: string;
  value: number;
  tags: Tag[];
  description: string;
}
