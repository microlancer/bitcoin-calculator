# Setup for XAMPP on Windows

Use admin terminal for commands! Right click CMD icon, run as Administrator.

First, fork the repo into your own account (e.g. myuser). You should
then have a repo like this:

https://github.com/myuser/bitvest

Then, clone it as bitvest-repo.

```
cd C:\xampp\htdocs\
git clone myuser@https://github.com/myuser/bitvest.git bitvest-repo
```

Add upstream:

```
git remote add upstream https://github.com/thorie7912/bitvest.git
```


Create a symlink:

```
mklink /D bitvest bitvest-repo/public
```

Fix the bootstrap symlink:

```
cd C:\xampp\htdocs\bitvest-repo\public
del bootstrap
mklink /D bootstrap bootstrap-2.3.2
```

Copy the config file:

```
cd C:\xampp\htdocs\bitvest-repo
copy config.php-example config.php
```

Copy the htaccess file:

```
cd C:\xampp\htdocs\bitvest-repo\public
copy .htaccess-example .htaccess
```

Then you should be able to browse to: http://localhost:8080/bitvest/ and see the welcome page.

Later on, if you want to sync master:

```
git fetch upstream && git checkout master && git merge upstream/master
git push origin HEAD
```

When you start work, you should be on a branch:

```
git checkout -b 'here_is_my_branch'
```

You can add files, commit, and push your branch up to your repo.

```
git add .
git commit -m 'I made some changes for ticket #123'
git push origin HEAD
```

Then go to your repo and create a Pull Request to the upstream branch which will wait for my approval.
