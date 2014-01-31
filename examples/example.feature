@tokenizer
  Feature: What do I see in the hook
  
    Scenario: SL Guinea pig page
      Given I am on "http://saucelabs.com/test/guinea-pig"
      Then I should see "i appear 3 times"
