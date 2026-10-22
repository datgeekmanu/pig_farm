import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { listings } from "@/lib/mock-data";
import type { Listing } from "@/lib/models";
import { queryKeys } from "@/lib/query-keys";

// TODO(#10.7): publishing must update herd count + inventory in one API transaction.
export function useListings() {
  return useQuery({
    queryKey: queryKeys.listings.all(),
    queryFn: async () => listings,
  });
}

export function useListing(lotId: string) {
  return useQuery({
    queryKey: queryKeys.listings.detail(lotId),
    queryFn: async () => {
      const listing = listings.find((l) => l.lotId === lotId);
      if (!listing) throw new Error(`Aucune annonce pour le lot ${lotId}`);
      return listing;
    },
  });
}

export function usePublishListing() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (listing: Listing) => ({ ...listing, published: true }),
    onSuccess: (listing) => {
      queryClient.setQueryData(queryKeys.listings.detail(listing.lotId), listing);
      queryClient.setQueryData<Listing[]>(queryKeys.listings.all(), (all) =>
        all?.map((l) => (l.lotId === listing.lotId ? listing : l)),
      );
    },
  });
}
