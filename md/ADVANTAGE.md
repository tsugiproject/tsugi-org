Using LTI Advantage with Tsugi
==============================

LTI Advantage (LTI 1.3) is the latest integration standard between tools and
LMS systems.  LTI Advantage uses OAuth 2.0 and JSON Web Tokens (JWTs) and
so it is more complex to set up than LTI 1.1 - which just needed a key and
secret.  Tsugi is certified as LTI Advantage compliant.   These instructions
can help guide you through your LTI Advantage installation.

Instructions/Details for Particular LMS Systems
-----------------------------------------------

We proves some detail specific to particular LMS systems:

* <a href="ADVANTAGE_LEARN.md">Blackboard Learn</a>
* Canvas (Coming soon)
* <a href="ADVANTAGE_SAKAI.md">Sakai</a>
* Moodle (Coming soon)

This document covers the basics and concepts of LTI Advantage
that apply to any LMS system as well as the strategy for migrating
LTI 1.1 to LTI Advantage in general and within Tsugi in particular.

Data Required by the LMS
-------------------------

These are the data fields that Tsugi will provide to the LMS:

* Tool OpenID Connect/Initialization Endpoint (1)

* Tool OpenId Connect Redirect Endpoint(s)

* Tool Public Key (most common)

* Tool OAuth2 Keyset URL (some LMS's prefer this instead of the Tool Public Key)

Some LMS's will give the tool its public key, and other LMS's will accept a public
key from the tool.  Other LMS's will expect a keyset URL in lieu of a public key.
The public key and/or the keyset url is available after the Issuer is
created in Tsugi.  If the LMS wants all the values before it starts its process,
you will need to create the Issuer in Tsugi with some dummy values and then edit
the Issuer in Tsugi later and put in the real values.

Once the issuer has been created in the LMS they will also need the `target_link_uri`.
This is most similar to the LTI 1.1 `launch_url`.   It is the actual launch destination
after the OIDC Connect flow has been completed.

For Tsugi if you are intending to send `Deep Linking Requests` (formerly known
as `Content Item Launches` to select tools and/or content use the endpoint like:

* https://www.tsugicloud.org/tsugi/lti/store/

If you want to send normal launch requests, you need to choose an actual installed
tool end point like:

* https://www.tsugicloud.org/mod/cats

* https://www.tsugicloud.org/mod/lmstest

For more detail on how these parameters are used in LTI Advantage, see
<a href="ADVANTAGE_INSIDE.md">Inside Advantage Launches and Services</a>.

Data Required by Tsugi (the Tool)
---------------------------------

These are the data fields that the LMS needs to provide to Tsugi:

* Issuer

* Client Id

* Deployment Id

* Platform OIDC Connect Authentication URL

* Platform OAuth2 Keyset URL

* Platform OAuth2 Token Retrieval URL

All these values except `deployment-id` go into the Issuer entry in Tsugi.
The `deployment-id` goes into the Tenant/Key in Tsugi.  Keyset urls are
not password protected.   Since they only hold public keys, there is
no particular need to hide or shield them.

For more detail on how these parameters are used in LTI Advantage, see
<a href="ADVANTAGE_INSIDE.md">Inside Advantage Launches and Services</a>.

How Different LMS Systems handle the Tool Public and Private Key
----------------------------------------------------------------

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

To create a tenant/key to receive launches, once you have successfully configured the issuer in
Tsugi and the LMS, insert a new key.  Make the OAuth Consumer Key be the client-id from the LMS
and leave the consumer secret blank.  Set the `deployment_id` from the information provided
by the LMS.  Select the issuer you just created from the drop-down and save the key.

Caliper key and Url are optional.   The `user_id` is also optional and can left as zero
since for now in Tsugi LTI Advantage does not have an end-user provisioned option.

Migrating an LTI 1.1 Key/Tenant to LTI Advantage
------------------------------------------------

Go into an exsiting tenant/key with an `oauth_consumer_key` and secret.  Set the `deployment_id`
(string) and select the issuer and save the entry.  Tsugi sill simultaneously accept LTI 1.3 launches for
(issuer, client-id, and `deployment_id`) and LTI 1.1 launches with `oauth_consumer_key and secret`.

If you want to later disallow LTI 1.1 launches for this tenant/key, simply set the secret to be empty.

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

