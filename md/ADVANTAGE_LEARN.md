
Connecting Tsugi and Blackboard Learn with LTI Advantage 
========================================================

This is a suppliment to the Tsugi LTI Advantage documentation available at
<a href="./ADVANTAGE.md" target="_blank">www.tsugi.org/ADVANTAGE.md</a>.

The <a href="https://www.blackboard.com" target="_blank">Blackboard</a>
approach is to mint the tool's public and private keys and not
require a tool keyset URL.

Set up should be simple, go into your Tsugi, start the process of creating a new issuer,
and then take the `OIDC Connect` and `OIDC Redirect` endpoints and put them into the
"new client" screen in Blackboard.  Once the client is created in Blackboard, you should have all
the information you need to fill out the Issuer and Tenant/Key data within Tsugi.

The issuer for Blackboard is always https://blackboard.com regardless of client.  Each client
(i.e. Tsugi instance) will get a unique client-id to be entered into the issuer and
deployment-id that is part of the key / tenant.

Tusgi will not properly handle launches unless there is an issuer/client-is combination and it
is associated with a tenant/key with the proper deployment-id.

Notes
-----

BB does not give a public key - Enter any string


