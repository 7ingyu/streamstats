import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import QueryWrapper from '@/Layouts/QueryWrapper';
import { Head } from '@inertiajs/inertia-react';
import { useGetFollowStreams } from '@/Hooks';

function Content({ auth, errors, ...props }) {

    const { data, isSuccess, isLoading, isError } = useGetFollowStreams();

    return (
        <AuthenticatedLayout
            auth={auth}
            errors={errors}
        >
            <Head title="Dashboard" />

            <div className="container">
                <div className="row">
                    <div className="col-12">
                        {!!isLoading && (
                            <div className="w-100 h-100 d-flex justify-content-center align-items-center p-5">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        )}
                        {!!isSuccess && (
                            <div
                                dangerouslySetInnerHTML={{__html: data}}
                            />
                        )}
                        {!!isError && (
                            'Error: Could not load data'
                        )}
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