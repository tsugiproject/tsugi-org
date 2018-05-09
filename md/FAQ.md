
I was asked a few questions off line and wanted to share the answers more broadly:

(1) Will Tsugi support more than one database (i.e. something other than MySQL/MariaDB)?

We were badly burned in the Sakai project trying to maintain database portability across more than one database - it made optimization and performance tuning very difficult and when we used Hibernate to alleviate database portability we ran into a whole new series of problems.   So for Tsugi I picked one database and have not looked back.   I picked MySQL/MariaDB because it is far easier to install a dev environment for a beginning developer (i.e. in my Web Applications for Everybody ww.wa4e.com course).  I want to expand the number of developers around the world able to build performant tools and MySQL without an ORM seemed to be the best approach.

(2) How can we be assured that tools “behave” in terms of only looking at data that belongs to them and not leaking data?

In terms of the data, the approach is to review all of the tool code before it is installed on your server.  The tools are supposed to avoid looking at data that does not belong to them.  It is as bad to show data from a different course as it is to show data from a different tool.  As such code review will be a critical element and also open course tools are a nice insurance policy to make sure tools of not intentionally leak data outside of the Tsugi hosting environment.  In terms of access to data in the database, the PDOX class was designed from the beginning to “wrap” all access to the database - a Tsugi tool could function in a situation where there was no actual DB connection in the PHP environment in which it was running but instead all queries and responses were sent to another server that has the actual database connection - this would allow for 100% logging / auditing of every DB query made by a tool.  I have designed but not yet built this functionality since I feel that for the first few years, code review and open source will be sufficient for the risk.

(3) What is Tsugi’s approach to “informed consent and the upcoming EU privacy laws.

In terms of informed consent - that is something we just need to build in.  I am unaware of all the use cases, but 200% committed to making whatever changes that are needed to comply with the letter and intent of the strictest privacy and informed consent laws.  I will need help of course and that is not my expertise and in the US it is all “privacy theatre” and in most of Europe it is also “privacy theatre” rather than real - but Tsugi (and LTI) was designed from the ground up to support state of the art privacy and informed consent.  So I will need help here.   Perhaps a good subject for an EU grant to invest in open source :)

/Chuck

