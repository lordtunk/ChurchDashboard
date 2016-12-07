#ChurchDashboard
A web site to manage attendance and follow ups.

It was originally based off of [Google Web Starter Kit](https://developers.google.com/web/tools/starter-kit/) which is where the gulpfile came from. The style is now based off [Bootstrap](http://getbootstrap.com/). The theme can be [customized](http://getbootstrap.com/customize/) using the config file in app/bootstrap/config.json

##Pages

###Attendance
Tally attendance for first and second service for a particular campus, the people are separated into Adults and Kids, Active and Inactive. Can also view an individuals attendance history and add people. Totals are displayed for both adults and kids and first and second service. Service types and campuses can be added, removed, and updated.

###Reports
Generate reports for attendance by date, attendance by person, MIA - people who have missed the last two Sunday services, follow ups, and you can view people by attender status

###Address View
After providing some filter criteria, the user can select multiple people and view their addresses on a map at once.

###Follow Up Entry
Easily add follow ups to one or more people

###Search
Search for a person by name

###Settings
Manage some dropdowns and options used throughout the system

## Prerequisites

### [Node.js](https://nodejs.org)

Bring up a terminal and type `node --version`.
Node should respond with a version at or above 0.10.x.
If you require Node, go to [nodejs.org](https://nodejs.org) and click on the big green Install button.

### [Gulp](http://gulpjs.com)

Bring up a terminal and type `gulp --version`.
If Gulp is installed it should return a version number at or above 3.9.x.
If you need to install/upgrade Gulp, open up a terminal and type in the following:

```sh
$ npm install --global gulp
```

*This will install Gulp globally. Depending on your user account, you may need to [configure your system](https://github.com/sindresorhus/guides/blob/master/npm-global-without-sudo.md) to install packages globally without administrative privileges.*


### Local dependencies

Next, install the local dependencies:

```sh
$ npm install
```

##Commands
### Watch For Changes & Automatically Refresh Across Devices

```sh
$ gulp serve
```

This outputs an IP address you can use to locally test and another that can be used on devices
connected to your network.
`serve` does not use [service worker](http://www.html5rocks.com/en/tutorials/service-worker/introduction/)
caching, so your site will stop being available when the web server stops running.

### Build & Optimize

```sh
$ gulp
```

Build and optimize the current project, ready for deployment.
This includes linting as well as image, script, stylesheet and HTML optimization and minification.
Also, a [service worker](http://www.html5rocks.com/en/tutorials/service-worker/introduction/)
script will be automatically generated, which will take care of precaching your sites' resources.
On browsers that [support](https://jakearchibald.github.io/isserviceworkerready/) service
workers, the site will be loaded directly from the service worker cache, bypassing the server.
This means that this version of the site will work when the server isn't running or when there is
no network connectivity.

### Performance Insights

```sh
$ gulp pagespeed
```

Runs the deployed (public) version of your site against the [PageSpeed Insights](https://developers.google.com/speed/pagespeed/insights/) API to help you stay on top of where you can improve.
