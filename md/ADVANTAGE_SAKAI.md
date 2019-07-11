Connecting Tsugi and Sakai with LTI Advantage
=============================================

This is a suppliment to the Tsugi LTI Advantage documentation available at
<a href="./ADVANTAGE.md" target="_blank">www.tsugi.org/ADVANTAGE.md</a>.

<a href="https://www.sakailms.org" target="_blank">Sakai</a> expects to mint the tool
private keys as of Sakai-19.0.  But you can also create the integration in Sakai and then
override the Sakai-chosen tool keys by editing the entry after it is created.  There are
plans to add support for the tool keyset in a later release of Sakai 19.x.

The workflow between Sakai and Tsugi is quite easy if you can be in the
admin UI of both tools at the same time.  This can either happen if both systems
are administered by the same person or they can work together exchanging values
over Slack or email.

Tsugi has a self-service mechanism to request and approve LTI 1.1 keys but does not yet
have a self service mechanism to request LTI Advantage keys so you need to create an Issuer.

How to Connect Tsugi into Sakai
-------------------------------

You can either connect a single tool endpoint in Tsugi or you can add Tsugi as a 
Learning App (Content Item or Deep Linking).  The process is the same except for 
a different URL and few checkboxes at the bottom of the add LTI tool screen.

For a single tool, simply check

* Direct tool url like https://www.tsugicloud.org/mod/cats

* Allow the tool to be launched as a link (only check this)

When intalling Tsugi as an App Store under Learning Apps, check

* App Store endpoint like https://www.tsugicloud.org/tsugi/lti/store/

* Allow external tool to configure itself

* Allow the tool to be used from the rich content editor to select content

* Do not check "Allow the tool to be launched as a link"

Adding Tsugi to Sakai Using LTI Advantage
-----------------------------------------

First go into the Tsugi Administrator UI and select 'Manage Keys'.
Add an Issuer.   On the issuer screen you can see the `OIDC Connect`
and `OIDC Redirect` endpoints.

In Sakai go to Adminstration Workspace, External Tools.  If you already have an
LTI 1.1 key/secret, edit it and turn on LTI 1.3 and enter the `OIDC Connect`
and `OIDC Redirect` endpoints and save the tool.  Then go into the
tool viewer on Sakai and you will see all the values including the
public and private key for the tool that can be pasted into the Tsugi
Add Issuer screen.

If you want Tsugi to generate its own keys, leave them blank on the Tsugi Add Issuer screen and
add the issuer.  Then view the issuer and find the public key.  Edit the tool entry in Sakai
and overwrite the `Tool Public Key`.  If you do this, you should delete/empty the `Tool Private
Key` in the Sakai tool entry as Sakai really has no need for the Tool's private key and it is
bad security for Sakai to posess the Tool's private key.

Then add or update a tenant/key in Tsugi with the `deployment_id` (always 1 on Sakai for now)
and select the issuer.

You should be ready to define a tool placement in Sakai and do a launch.  One fun aspect of Sakai
is that once you set up a tool with both LTI 1.1 and LTI 1.3 values, you can switch back and forth
between 1.1 and 1.3 launches by simply changing the LTI 1.3 radio button.

You can work through this example using the Sakai and Tsugi nightly servers.  They are nice to
experiment with because they reset every night :)

https://trunk-mysql.nightly.sakaiproject.org/portal/  ( admin / admin )
https://dev1.tsugicloud.org/tsugi/admin/ (tsugi)


