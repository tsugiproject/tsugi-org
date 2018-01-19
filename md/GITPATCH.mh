Working on A Tsugi Tool
-----------------------

Lets say you want to work on a tool like the youtube tool.   First go into github.com

    https://github.com/tsugitools/youtube

And "Fork" the repo into your own account.  It will take a moment for GitHub to make the fork
and then you will be put into your fork.

    https://github.com/csev/youtube

Then on your local computer check out your fork (because you don't have permission to write
to the one in 'tsugitools').

    cd tsugi/mod
    git clone https://github.com/csev/youtube.git

Add a "link back" to the original repo that you forked from, using the following command:

    cd youtube
    git remote add upstream https://github.com/tsugitools/youtube

Then make your changes and test them.  When you like your changes, you make a 
branch in your repository - give it a name different from other branches and then commit 
your changes with a nice commit message.

    git checkout -b patch1
    git commit -a

Then push your changes into the branch into *your repo* - your repo is "origin" - where
the code was downloaded from.

    git push origin patch1

Then in a browser go back to your repo in github and you should see a little message:

    "Your recently pushed branches:"

And a green button to "Compare and pull request" - click the button and submit the patch to the
owners of the repository that you forked from.  The owners will review your patch and may ask for changes
or most likely just merge the patch.   If it is Chuck, you might have to send a note and say,
"Hey - take a look at my pull request".

Once the PR (Pull request) is merged, go back to your local copy:

    cd tsugi/mod/youtube
    git checkout master   (to get back on the master branch)
    git pull upstream master  (pull your changes back down from the original repo)
    git push origin master  (put your changes in the master branch in your repo)

Now your changes are round-tripped.  You can go in and delete the "patch1" branch at this
point.

Resources
---------

* Simple Cheat Sheet: http://rogerdudler.github.com/git-guide/files/git_cheat_sheet.pdf
* Video: What is Version Control http://git-scm.com/video/what-is-version-control
* Video: What is Git? http://git-scm.com/video/what-is-git

