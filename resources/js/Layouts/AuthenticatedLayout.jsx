import React, { useState } from 'react';
import { Link } from '@inertiajs/inertia-react';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';


export default function Authenticated({ auth, children }) {
    // const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

    return (
        <>
            <div className="container">
                <div className="row justify-content-between my-5">
                    <div className="col-auto">
                        <h1>Welcome, {auth?.user?.username}!</h1>
                    </div>
                    <div className="col-auto">
                        <ResponsiveNavLink method="post" href={route('logout')} as="button">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
            <hr/>
            <div className="pt-5">
                {children}
            </div>
        </>
    );
}
