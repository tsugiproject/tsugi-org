Inside LTI Advantage Launches and Services
==========================================


This is a suppliment to the Tsugi LTI Advantage documentation available at
<a href="./ADVANTAGE.md" target="_blank">www.tsugi.org/ADVANTAGE.md</a>.

Inside An LTI Advantage Launch
------------------------------

What we used to call an "LTI 1.1 Launch URL" is best called a "Deep Link" in LTI Advantage.  It is the actual
tool or content endpoint in the Tool system.  The ultimate URL can be the same for LTI 1.1 and LTI Advantage, but
when LTI Advantage launches happen, they don't just send data straight to the launch URL.  There is a multi-step
process this is closely modelled on the OpenID Connect logic flow.

The user starts the process by clicking on a link in the LMS called "Chapter 1" which ultimately wants to
launch to a tool url like:

    https://my.cool.stuff.com/books/algebra/chapter-1

In LTI 1.x, the LMS signs a bunch of POST values with the OAuth 1.0 key and posts to that URL.  In LTI Advantage
there are more steps:

1. The LMS sends the browser to the `Tool OpenID Connect/Initialization Endpoint` with the
Issuer as a parameter.  The Tool receives this and generates a `state` value to uniquely identify
the current user's browser and then...

2. The tool then sends the browser back to the `Platform OIDC Connect Authentication URL`.  The LMS pulls out
the `state` value and then...

3. The LMS produces a JWT of all the launch data and the `state` value and signs it, generates an HTML form
with an `id_token` field and then..

4. The form is auto-submitted to the `Tool OpenId Connect Redirect Endpoint` which receives the data, checks
the `state`, and if the state matches what the tool expects, redirects to the original "deep link"/"launch url".

5. At the launch url, the JWT is parsed. Within the JWT header there is a key id field (`kid`).  The Tool
retrieves the `Platform OAuth2 Keyset URL` which is an array of keys, and loks up the Platform Public Key
in the array using the `kid` as key in the array.  Then that public is used to check the signature in the JWT.
If all is well, the user session is created and the user is logged into the deep link.

Note that clever tools are encouraged to locally cache the `kid` and corresponding public key locally to avoid
hitting the `Platform OAuth2 Keyset URL` on every launch to find the public key.

Note that the tool needs to implement the functionality in steps 4 and 5,
but things like where the JWT signature is checked can be in either step 4 or 5
and it does not matter to the outside world.   Tsugi does steps 4 and 5 exactly as described above so as to keep
launch logic to deep links very parallel between LTI 1.1 and LTI Advnatage and to ease transition from LTI 1.1
to LTI Advantage.   Tools that don't care about LTI 1.1 to LTI Advantage transition may deside to combine steps
4 and 5 into a single step.

Using Services in LTI Advantage
--------------------------------

The pattern for communicating with LMS services is based on OAuth 2.0.  At configuration time, the Tool
is provided a `Platform OAuth2 Token Retrieval URL`.

If the tool has access to the Names and Roles or Outcome services, the URLs to these services are provided
to the tool as part of the launch data in the JWT.

If the tool wants to talk to these services, it must first get an "API Token".  This is done by
making a bit of JSON that includes the `scopes` that indicate which permission the tool is about
to use (i.e. like the permission to read roles or the permission to create grade book columns).
These scopes are encoded into a JWT along with the `Client Id` and signed by the `Tool Public Key`
and sent to the `Platform OAuth2 Token Retrieval URL`.

If the LMS validated the JWT based on the `Tool Public Key` associated with the `Client ID` and
the Client is allowed to have the permissions requested in the `scope` values, the LMS generates
a token (typically with a 3600 second expiration) and returns it to the Tool.

The tool then communicates with the API endpoint, simply incliding the Token as one of the HTTP
headers.

The tools are encouraged to cache the tokens for the duration of their validity (less then an hour)
and only re-request tokens periodically.

References
----------

* JSON Web Tokens - https://tools.ietf.org/html/rfc7519

* OAUth 2.0 - https://oauth.net/2/bearer-tokens/

* OAuth 2.0 RFC - https://tools.ietf.org/html/rfc6749

* The OAuth 2.0 Authorization Framework: Bearer Token Usage - https://tools.ietf.org/html/rfc6750

