import React, { useEffect } from 'react';
import { Link, Head } from '@inertiajs/inertia-react';

export default function Home ({ auth, clientId, appUrl, csrf, ...props }) {

    const oathUrl = `https://id.twitch.tv/oauth2/authorize
      ?response_type=code
      &client_id=${clientId}
      &redirect_uri=${appUrl}/oauth
      &scope=user%3Aread%3Aemail+user%3Aread%3Afollows
      &state=${csrf}`.replace(/\s/g, '');

    // Redirect to dashboard if already logged in
    useEffect(() => {
      if (!!auth.user) window.location.href = '/dashboard';
    }, [auth])

    return (
        <>
            <Head title="Welcome" />
            <div className="container d-flex flex-column justify-content-center align-items center pt-5">
              <h1 className="text-center">StreamStats</h1>
              <a className="btn btn-primary" href={oathUrl}>Log In</a>
            </div>
        </>
    );
}
