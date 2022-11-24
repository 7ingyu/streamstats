import React, {useState} from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import QueryWrapper from '@/Layouts/QueryWrapper';
import { Head } from '@inertiajs/inertia-react';
// import { useGetFollowStreams } from '@/Hooks';
import { StreamsPerGame } from '@/Components/dashboardComponents';

// props = {
//     auth,
//     errors,
//     userStreams,
//     topStreams,
//     sharedTags,
//     needForTop100,
//     followedTopStreams,
//     streamsPerHr,
//     topStreamsAsc,
//     topSteamsDesc,
//     medianViews,
//     gamesByViewers,
//     streamsPerGame,
//  };

function Content({auth, errors, ...props}) {

    // const { data, isSuccess, isLoading, isError } = useGetFollowStreams();

    return (
        <AuthenticatedLayout
            auth={auth}
            errors={errors}
        >
            <Head title="Dashboard" />
            {/* Total number of streams for each game
            Top games by viewer count for each game
            Median number of viewers for all streams
            List of top 100 streams by viewer count that can be sorted asc & desc
            Total number of streams by their start time (rounded to the nearest hour)
            Which of the top 1000 streams is the logged in user following?
            How many viewers does the lowest viewer count stream that the logged in user is following need to gain in order to make it into the top 1000?
            Which tags are shared between the user followed streams and the top 1000 streams? Also make sure to translate the tags to their respective name? */}

            <div className="container">
                <div className="row">
                    <div className="col-12">
                        <StreamsPerGame {...props} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default function Dashboard(props) {
    return (
        <QueryWrapper Component={Content} {...props} />
    )
}