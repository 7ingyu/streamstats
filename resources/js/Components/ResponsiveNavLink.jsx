import React from 'react';
import { Link } from '@inertiajs/inertia-react';

export default function ResponsiveNavLink({ className = null, method = 'get', as = 'a', href, active = false, children }) {
    return (
        <Link
            method={method}
            as={as}
            href={href}
            className={`${className ? className : 'btn btn-primary'}`}
            disabled={!active.toString()}
        >
            {children}
        </Link>
    );
}
