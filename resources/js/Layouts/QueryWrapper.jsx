import { useState, useEffect } from "react";
import { QueryClient, QueryClientProvider } from "react-query";
import { ReactQueryDevtools } from "react-query/devtools";

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            retry: 0,
            refetchOnWindowFocus: false,
            staleTime: Infinity,
        },
    },
});

export default function QueryWrapper({ Component, ...props }) {
    return (
        <QueryClientProvider client={queryClient} contextSharing={true}>
            <Component {...props} />
            {/* <ReactQueryDevtools initialIsOpen={false} /> */}
        </QueryClientProvider>
    );
}
