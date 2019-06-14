# Request Processor

## License

MIT

## About

This project is a web application showing sequences of forms to users for providing input. This input - considered a _request_ - is collected in a server-side database. In addition a confirmation mail is sent to requesting user for validating a request. After successful validation the site admin is notified on every received and validated request.

Apart from that there are some additional features for the site admin to monitor pending requests and fetch user input.

## Prerequisites

The application may be installed on a server running PHP7. In addition a MySQL 5.x database must be available on server. An SMTP server is used for sending mails to users and site administrator.

As an option the application may be run as a server consisting of docker containers.

## Install

For installation you need to check out this project or grab a copy of all files.

### Install Dependencies ...

The project depends on third-party libraries to be installed on server. This is basically achieved by using the tool [composer](https://getcomposer.org). This tool must be installed on your client or server unless using [docker](https://www.docker.com/).

#### ... With Composer

1. Open command line and change to folder **src/**.
2. Run `composer install`.
 
#### ... With Docker 

1. Open command line.
2. Run `docker run --rm -itv /path/to/local/project/src:/app composer` with **/path/to/local/project** replaced with absolute pathname of folder containing your local copy of project. When using Windows host this might look like this: `docker run --rm -itv c:\users\myuser\desktop\project\src:/app composer`.

### Using Dedicated Web Space For Hosting

1. Upload content of folder **src/** to your web space.
2. Mark folder **public/** as document root or ensure by any other means that only folder **public/** is exposed for access.
3. Copy file **setup/server-example.yaml** to **setup/server.yaml**.
4. Use text editor to adjust copied file **setup/server.yaml**. You need to provide the following information there:
   * your database configuration
   * your site administrator's mail address
   * the password required for accessing admin functions

### Using Docker For Hosting

With docker you can run the application in a set of application containers commonly known as a docker service. This requires [docker](https://www.docker.com/) to be installed on your host or server. Follow these steps _when docker is installed:_ 

1. Copy file **src/setup/server-docker.yaml** to **src/setup/server.yaml**.
2. Use text editor to adjust copied file **src/setup/server.yaml**. You need to provide the following information there:
   * your site administrator's mail address
   * the password required for accessing admin functions
3. Open command line in project's root folder and run `docker-compose up -d`. This will build required containers on first run and start serving content of folder **src/** in background.

## How To Configure

The application strongly relies on our [forms processor](https://github.com/cepharum/forms-processor). In fact it is one of several showcases for that project by combining it with a medium-size backend to collect user information. 

Due to depending on forms-processor configuration is mostly about creating configurations for the forms-processor and so documentation of that processor applies here as well. However, there are additional features to be discussed in this project's documentation.

### Create Request

In folder **config/** there are YAML-formatted files each representing another kind of request available to users to perform (except for **defaults.yaml** which is used to define a common set of information shared by all other files in there).

Initially there is **example.yaml** which can be copied into different YAML file. You may choose any name you like but need to use extension **.yaml**. The name will be used as part of a URL query used for processing requests later, so you might want to stick with latin letters, digits and dashes to get a pretty URL. For the sake of this quick start tutorial you might want to name it **appointment.yaml**.

This new configuration instantly instantly exposes support for a new kind of _request_ which is named **appointment** and may be used with URL like this one:

https://your-server.com/?request=appointment

(assuming you've set up the application on a web space available at **your-server.com** with support for secure access via HTTPS).

For configuration you should open the file **appointment.yaml** with a text editor and change its content according to your needs. The file's hierarchy consists of a few separate sections:

* **form** contains definition of forms a user needs to pass when starting request. All data in this section is used by forms-processor and therefore any documentation of forms-processor regarding definition of forms applies here.

* **validation** customizes validation mails sent to any requesting user's mail address. This section consists of a mail's **subject**, its **body**, an address the mail is to be sent **from** instead of site administrator's mail address, some optional list of addresses mails should be sent to as **bcc**. Boolean option **html** controls whether sending HTML or plain-text mail.

  In addition two special options may be used here: **onSuccess** provides a string to display on successfully confirming validity of a user's request. Accordingly, **onFailure** provides a string to show in case of validation fails.

* **notification** customizes notification mails sent to site administrator after user has validated his/her request. It basically works like **validation** above with a few differences:

  * It is possible to provide a custom sender the mail is sent **to**.
  * You can control whether attaching user's input in **csv** format and/or in **yaml** format using boolean values on either option.

* Options **title** and **teaser** can be set optionally which results in listing this kind of request on overview which is presented in case of user is opening web application without selecting particular request.
