Using LTI Advantage with Tsugi
==============================

LTI Advantage (LTI 1.3) is the latest integration standard between tools and
LMS systems.  LTI Advantage uses OAuth 2.0 and Java Web Tokens (JWTs) and
so it is more complex to set up than LTI 1.1 - which just needed a key and
secret.  Tsugi is certified as LTI Advantage compliant.   These instructions
can help guide you through your LTI Advantage installation.

In LTI 1.1, the tool "minted" the consumer key and secret and somehow
communicated it to the LMS admin or instructor who installed the tools.
LTI Advantage uses two ley/secret pairs - one from the LMS to the Tool and
another from the tool to the LMS.  There area also several URLs that are needed
on each side to allow communication.

It is easiest if you can do the setup at the same time in two browsers.  Tsugi
is designed to allow you to start the setup, send some data to the LMS Admin, and
then get some data back that you add to the entries in Tsugi.

Data Required by the LMS
-------------------------

These are the data fields needed by the LMS from the tool:

* Tool OpenID Connect/Initialization Endpoint (1)

* Tool OpenId Connect Redirect Endpoint(s)

* Tool Public Key (most common)

* Tool OAuth2 Keyset URL (some LMS's prefer this instead of the Tool Public Key)

Some LMS's will give the tool its public key, and other LMS's will accept a public
key from the tool.  Other LMS's will expect a keyset URL in lieu of a public key.

For more detail on how these parameters are used in LTI Advantage, see
the sections titled 'Inside An LTI Advantage Launch' and 'Talking to Services in LTI Advantage'.

Data Required by the Tool
-------------------------

* Issuer (from the Platform)

* Client Id

* Platform OIDC Connect Authentication URL

* Platform OAuth2 Keyset URL

* Platform OAuth2 Token Retrieval URL

Keyset urls are not password protexted.   Since they only hold public keys, there is
no particular need to hide or shield them.

For more detail on how these parameters are used in LTI Advantage, see
the sections titled 'Inside An LTI Advantage Launch' and 'Talking to Services in LTI Advantage'.

How the Tool Public and Private Key are Generated
-------------------------------------------------

There is some variation on how LMS systems expect the Tool Public and Private Keys
to be generated.

The most commmon variation is that the LMS generates the Public
and Private keys and tells the tool the key values at the moment the tool is integrated
into the LMS.   These then must be copied into the Tool.  This approach
has the advantage that tools do not need to know how to make key pairs and the LMS
can tell the tool to change their keys using some for of out of band communication.

The other variation is that the Tool generates the key pair, and then shares
its public key with the LMS using a keyset URL.  This is the more traditional way
that two-way OAuth 2.0 is set up.  It has the advantage that the tool's private key is
never sent across the Internet or shown in a UI.  The disadvantage is that there is
more code that needs to be written in the tool to handle the scenario.

We can expect that LMS vendors will take one or the other approaches and so tools
will need to be flexible.  Tsugi handles both scenarios.

Throughout the document there will be steps like "if the LMS is making the Tool keys..."
where we call out differences in the processes.

Creating an Issuer in Tsugi
---------------------------

The issuer captures all the details if the keys, secrets, urls and security contract
with the LMS.   Before you can create an access key (might be better called the tenant\_key),
you need to ceate an issuer.

Go into the Tsugi Administration tool and select `Manage Access Keys`.  Add a `New Issuer`.
You will see a screen that shows the `OIDC Connect` and `OIDC Redirect` endpoints.  They
are the same across all issuers.  You will see that the `OAuth 2.0 KeySet` is not yet
defined because it is different for each issuer.  You can see the complete keyset URL
after you create the issues and view its data.

(Scenario A - LMS Mints Tool Keys)

If the LMS will be issuing the tool's public and private keys it should not need the
KeySet url.  It should be able to set up your Tsugi instance with just the `OIDC Connect`
and `OIDC Redirect` endpoints.

The go into the LMS and create your integration.   When it is done, you should get back
the issuer, client id, deployment id, platform keySet URL, platform token retrieval URL, tool public key
and tool private key.

All of the LMS values except deployment id are entered into the `Add Issuer` screen
and you can add the Issuer.

Continue to Creating a Tenant / Key below.

(Scenario B - Tsugi Mints Tool Keys)

This is a bit trickier because the LMS may want the complete Tool keyset URL before giving
you any of their data.  It is somewhat of a chicken and egg problem.  But it is easy to solve.

Create an Issuer with fake random data for iany of the fields (issuer, client-id, key set URL,
token retrieval URL) that the LMS won't provde you in advance.  Leave the platform public key blank
as it normally is retrieved form the platform keyset URL.
Leave the two tool keys blank so Tsugi mints them and save the issuer.

Then immediately view the issuer.  At that point you should see all the data that
the LMS needs.  Typically the LMS will need the tool public key, the tool keyset url,
openID connect endpoint, and tool redirect endpoint.

Provide these values to your LMS and they should be able to create your client
in their system and give you back their issuer, your client-id in their system,
and the lms keyset URL if you don't already have it.

Now edit the issuer entry in Tsugi, and replace the fields you filled with random stuff
with the real values and save the issuer.

Tenant / Keys in Tsugi
----------------------

Once you have a new Issuer, you can associate the issuer with a tenant/key.
A "tenant" is like the `oauth_consumer_key` from LTI 1.1.
In LTI 1.3/Advantage the `deployment_id` within an issuer/client-id pair maps to a tenant.

The key structure is where you solve the problem of migrating from LTI 1.1 to LTI Advantage
if you already have an LTI 1.1 relationship with an LMS.  Tsugi can simultaneously
support LTI 1.1 and LTI Advantage launches if your key/tenant is set up properly.

Creating a New LTI Advantage Only Key/Tenant
--------------------------------------------

To create a key/tenant to receive launches, once you have successfully configured the issuer in
Tsugi and the LMS, insert a new key.  Make the OAuth Consumer key be the client-id from the LMS
and leave the consumer secret blank.  Set the `deployment_id` from the information provided
by the LMS.
Select the issure you just created.
Caliper key and Url are optional.   The `user_id` is also optional.

Migrating an LTI 1.1 Key/Tenant to LTI Advantage
------------------------------------------------

Go into an exsiting tenant/key with an `oauth_consumer_key` and secret.  Set the `deployment_id`
(string) and select the issuer and save the entry.  You now have LTI 1.3 launches for
(issuer, client-id, and deployment_id) and LTI 1.1 launches with oauth_consumer_key and secret.
If you want to later disallow LTI 1.1 launches, simply set the secret to be empty.

Examples from Particular LMS Systems
====================================


Blackboard
----------

The <a href="https://www.blackboard.com" target="_blank">Blackboard</a>
approach is to mint the tool's public and private keys and not
require a tool keyset URL.

Set up should be simple, go into your Tsugi, start the process of creating a new issuer,
and then take the `OIDC Connect` and `OIDC Redirect` endpoints and put them into the
"new client" screen in Blackboard.  Once the client is created in Blackboard, you should have all
the information you need to fill out the Issuer and Tenant/Key data within Tsugi.

The issuer for Blackboard is always https://blackboard.com regardless of client.  Each client
(i.e. Tsugi instance) will get a unique client-id (issuer screen) and
deployment-id (key / tenant screen).

Canvas
------

Instructions are coming.

Sakai
-----

<a href="https://www.sakailms.org" target="_blank">Sakai</a> expects to mint the tool
private keys as of Sakai-19.0.  There are plans to add support for the tool
keyset in a later release of Sakai 19.x.   The workflow between Sakai and Tsugi is quite easy if you can
be in the admin UI of both tools at the same time.  This can either happen if both systems
are administered by the same person or they can work together exchanging values over Slack or email.

First go into Tsugi.   Add an Issuer.   On the issuer screen you can see the `OIDC Connect`
and `OIDC Redirect` endpoints.

In Sakai go to Adminstration Workspace, External Tools.  If you already have an LTI 1.1 key/secret, edit it
and turn on LTI 1.3 and enter the `OIDC Connect` and `OIDC Redirect` endpoints and save the tool.  Then go into the
tool viewer on Sakai and you will see all the values including the public an private key for the tool that can
be pasted into the Tsugi Add Issuer screen.

Then add or update a tenant/key with the `deployment_id` (always 1 on Sakai for now) and issuer.

You should be ready to define a tool placement in Sakai and do a launch.  One fun aspect of Sakai
is that once you set up a tool with both LTI 1.1 and LTI 1.3 values, you can switch back and forth
between 1.1 and 1.3 launches by simply changing the LTI 1.3 radio button.

You can work through this example using the Sakai and Tsugi nightly servers.  They are nice to
experiment with because they reset every night :)

https://trunk-mysql.nightly.sakaiproject.org/portal/  ( admin / admin )
https://dev1.tsugicloud.org/tsugi/admin/ (sakaiger)

Inside An LTI Advantage Launch
------------------------------

What we used to call an LTI Launch URL is best called a "Deep Link" in LTIAdvantage.  It is the actual
tool or content endpoint in the Tool system.  It can be the same for LTI 1.1 and LTI Advantage, but
when LTI Advantages launches happen, they don't just send data straight to the launch URL.  There is a multi-step
process this is closely modelled on the OpenID Connect logic flow.

The user starts the process by clicking on a link in the LMS called "Chapter 1" which ultimately wants to
launch to a url like:

    https://my.cool.stuff.com/books/algebra/chapter-1

In LTI 1.x, the LMS signs a bunch of Post values with the OAuth 1.0 key and posts to that URL.  In LTI Advantage
there are more steps:

1. The LMS sends the browser to the `Tool OpenID Connect/Initialization Endpoint` with the Issuer.  The Tool
receives this and generates a `state` value to uniquely identify the current user's browser and then...

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

Note that the tool needs to have steps 4 and 5, but things like where the JWT signature is checked can be in 4 or 5
and it does not matter to the outside world.   Tsugi does steps 4 and 5 exactly as described above so as to keep
launch logic to deep links very parallel between LTI 1.1 and LTI Advnatage.

Talking to Services in LTI Advantage
------------------------------------

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

A Sample Tsugi Issuer Entry
===========================

Issuer Key

    https://dev1.sakaicloud.com

LTI 1.3 Client ID (from the Platform)

    ebf1be93-7d8d-4a1f-a7de-f2c8f5407300

LTI 1.3 Platform OAuth2 Well-Known/KeySet URL (from the platform)

    https://dev1.sakaicloud.com/imsblis/lti13/keyset/12

LTI 1.3 Platform OAuth2 Bearer Token Retrieval URL (from the platform)

    https://dev1.sakaicloud.com/imsblis/lti13/token/12

LTI 1.3 Platform OIDC Authentication URL (from the Platform)

    https://dev1.sakaicloud.com/imsoidc/lti13/oidc_auth

LTI 1.3 Platform Public Key (Usually retrieved via keyset url)

    -----BEGIN PUBLIC KEY-----
    MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA2WE8VJ9ZJkNXq5+aqd6t
    dMCkAnkRP6Q7pIGj2zkyjMOJrzSpAfw5MSru+zUBdBddFqdmIV5tAG/r1NNFzrXd
    GQpOtduT3FWLJ4rdf9rYNrNHOXLn+yJwYtL/8QbFMJa79VYKAshJK2xBvj1VuEDQ
    qzlqOGOF+j/Z4yGSiYIvOZjy94+/DkKkfmL0lgWC6IDi/GJ3k6UvH8k2VNhqWgnn
    3kk5NWDig//jlflaMUYBADUiiPlOY9Jg/CipRrfSfoFrbZfSmN2n7DIaEHr8uCXv
    MUkj7p0sSkCkzopUO2GYVGkFLiY+ge2/g2h7I/r8EriFS1YFXqO+bs39fXN7FMA4
    KQIDAQAB
    -----END PUBLIC KEY-----

LTI 1.3 Tool Public Key (Provide to the platform)

    If this is present it is the most recently retrieved public key
    from the LMS's key set url.   This is rarely set by hand.

LTI 1.3 Private Key (kept internally only)

    -----BEGIN PRIVATE KEY-----
    MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDZYTxUn1kmQ1er
    n5qp3q10wKQCeRE/pDukgaPbOTKMw4mvNKkB/DkxKu77NQF0F10Wp2YhXm0Ab+vU
    00XOtd0ZCk6125PcVYsnit1/2tg2s0c5cuf7InBi0v/xBsUwlrv1VgoCyEkrbEG+
    PVW4QNCrOWo4Y4X6P9njIZKJgi85mPL3j78OQqR+YvSWBYLogOL8YneTpS8fyTZU
    2GpaCefeSTk1YOKD/+OV+VoxRgEANSKI+U5j0mD8KKlGt9J+gWttl9KY3afsMhoQ
         [SNIP]
    VdMtvyk/bHJfj+IycSdBRtnB4F8Fu/be4/lkQ+1xw0uyqhvVpP/8DW2dfYxkJXt8
    4lY57zXPWB1hohfF8I/sJIzx5+Q6b1UUu7dXfdtR9QKBgQDO7jR7tHtVFgSGSoSV
    yZZ+e6kNGoxv8pjse0XkebEt/hl69SJsp3bLJqD1Yz5PEc1b1Ic1XwFLtd4MGJEW
    MNGNVKezZtdWaEkkBVK39HA5vsC/Pcmvzx6xpDOhWnzKWr+mimfpgCB+dvKDRMcU
    quFl3jKbT+B69VPsCh5WTxxiBQ==
    -----END PRIVATE KEY-----

LTI 1.3 Tool Keyset Url (Extension - may not be needed/used by LMS)

    https://www.tsugicloud.org/tsugi/lti/keyset?issuer_id=1

LTI 1.3 OpenID Connect Endpoint

    https://www.tsugicloud.org/tsugi/lti/oidc_login

LTI 1.3 Tool Redirect Endpoint

    https://www.tsugicloud.org/tsugi/lti/oidc_launch

