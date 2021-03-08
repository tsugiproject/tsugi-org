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

* Tool OAuth2 Keyset URL

The keyset url is available after the Issuer is
created in Tsugi.  If the LMS wants all the values before it starts its process,
you will need to create the Issuer in Tsugi with some dummy values and then edit
the Issuer in Tsugi later and put in the real values.

Once the issuer has been created in the LMS they will also need the `target_link_uri`.
This is most similar to the LTI 1.1 `launch_url`.   It is the actual launch destination
after the OIDC Connect flow has been completed.

For Tsugi if you are intending to send `Deep Linking Requests` (formerly known
as `Content Item Launches`) to select tools and/or content use the endpoint like:

* https://www.tsugicloud.org/tsugi/lti/store/

If you want to send normal launch requests, you need to choose an actual installed
tool end point like:

* https://www.tsugicloud.org/mod/youtube

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

Sometimes the LMS needs our Tool keyset URL before giving you any of their
data values.  It is somewhat of a chicken and egg problem.  But it is easy to solve.

Create an Issuer with fake random data for any of the fields (issuer, client-id, key set URL,
token retrieval URL) that the LMS won't provde you in advance.  

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

Once you have a new Issuer / CLient-Id, you can associate the issuer with a tenant/key.
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

LTI 1.3 Tool Keyset Url

    https://www.tsugicloud.org/tsugi/lti/keyset?issuer_id=1

LTI 1.3 OpenID Connect Endpoint

    https://www.tsugicloud.org/tsugi/lti/oidc_login

LTI 1.3 Tool Redirect Endpoint

    https://www.tsugicloud.org/tsugi/lti/oidc_launch

