Feature: Docs Command

    Scenario:
        Given I run Waffle from the command line
        When I run "docs --no-browser"
        Then I should see "https://github.com/waffle-ops/waffle/wiki"
