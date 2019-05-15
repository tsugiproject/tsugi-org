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

* Tool OpenID Connect/Initialization Endpoint

* Tool OpenId Connect Redirect Endpoint(s) - Can be more than one

* Tool OAuth2 Keyset URL (Optional)

Data Required by the Tool
-------------------------

* Issuer (from the Platform)

* Client Id

* Platform OAuth2 Keyset URL

* Platform OAuth2 Token Retrieval URL

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
the public with the LMS using a keyset URL.  This is the more traditional way
that two-way OAuth 2.0 is set up.  It has the advantage that the tool's privat key is 
never sent across the Internet or shown in a UI.  The disadvantage is that there is
more code that needs to be written in the tool to handle the scenario.

We can expect that LMS vendors will take one or the other approaches and so tools
will need to be flexible.  Of course Tsugi handles both scenarios.

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

All of these values except deployment id are entered into the `Add Issuer` screen 
and you can add the Issuer.  Make note of the integer primary key by viewing the issuer in Tsugi.

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

Note the primary key of the issuer.  You will need that later.

Provide these values to your LMS and they should be able to create your client
in their system and give you back their issuer, your client-id in their system,
and the lms keyset URL if you don't already have it.

Now edit the issuer entry in Tsugi, and replace the fields you filled with random stuff
with the real values and save the issuer, noting the primary key of the issuer.

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

Migrating an LTI 1.1 Key/Tenant to LTI Advantage
------------------------------------------------

