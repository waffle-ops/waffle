# Who Doesn't Like a Good Waffle?

## :waffle: Waffle
Or `wfl` for short lets you keep project-specific config in a project, while also providing a standard way of setting up projects so that the tool can do the waffling so that _you_ can focus on the coding.

## Premise
I work on a lot of projects (probably too many). More often than not, something about the project setup no longer works and I spend way too much time waffling around be it updating dependencies, fixing bugs _caused_ by updating those dependencies, or anything and everything else that comes up when you have not touched a project in a month or two.

I work full time as a developer, and because of that, I don't have all that much time to devote to side projects outside of work. I want to spend as little time as possible fixing issues that prevent me from getting stuff done for each and every project. Most of my projects have a lot overlap. Waffle is intender to absorb as much of that overlap as possible and create a consistent api across everything that I work on.

## How it Works
The goal is to have a globally installed tool that can sync databases and files, update dependencies, run tests, etc -- controllable by a config file for each project. If all of the projects are set up using a common tool to automate the boring stuff. When something breaks, I should only need to fix it once. This tool can also be installed in a CI layer to take advantage of any tasks already automated by Waffle.

Waffle is also flexible and will be providing ways to change its behavior via a configuration file. More details will be provided as decisions have been made and progress has been achieved.

## Install Instructions
See [INSTALL.md](INSTALL.MD)

___

### FAQs

#### Can I use Waffle?
Couldn't we all use a waffle every now and then?

#### Can I depend on Waffle?
Sort of. This tool is mainly for me, but I'm willing to share. That being said, I may take the project in a direction you don't agree with. At the very least, you can be sure that I won't release a breaking change as a minor update or any nonsense like that.

#### Can I contribute?
You are welcome to contribute, but be warned &dash; Waffle is incredibly niche. I'm likely only  to accept things that will directly benefit the project for the use cases for which Waffle has been developed.

#### What types of projects is Waffle for?
Currently, Waffle is intended to support the following types of projects:
- Drupal 7
- Drupal 8
- WordPress

Hosting support is mainly targeting Acquia and Pantheon. Limited support for custom hosts will also be included. I have a few instances hosted on Linode that would also benefit from Waffle.
