import axios from "axios";
import { useQuery } from "react-query";

async function getFollowStreams() {
    let { data } = await axios.get(`/twitch/follows`);
    return data;
}

export function useGetFollowStreams(options) {
    return useQuery("twitch-follow", getFollowStreams, {
        ...options,
    });
}
