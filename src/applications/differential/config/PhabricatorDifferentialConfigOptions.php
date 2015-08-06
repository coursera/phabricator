<?php

final class PhabricatorDifferentialConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht('Differential');
  }

  public function getDescription() {
    return pht('Configure Differential code review.');
  }

  public function getFontIcon() {
    return 'fa-cog';
  }

  public function getGroup() {
    return 'apps';
  }

  public function getOptions() {
    $caches_href = PhabricatorEnv::getDoclink('Managing Caches');

    $custom_field_type = 'custom:PhabricatorCustomFieldConfigOptionType';

    $fields = array(
      new DifferentialTitleField(),
      new DifferentialSummaryField(),
      new DifferentialTestPlanField(),
      new DifferentialAuthorField(),
      new DifferentialReviewersField(),
      new DifferentialProjectReviewersField(),
      new DifferentialReviewedByField(),
      new DifferentialSubscribersField(),
      new DifferentialRepositoryField(),
      new DifferentialProjectsField(),
      new DifferentialViewPolicyField(),
      new DifferentialEditPolicyField(),

      new DifferentialDependsOnField(),
      new DifferentialDependenciesField(),
      new DifferentialManiphestTasksField(),
      new DifferentialCommitsField(),

      new DifferentialJIRAIssuesField(),
      new DifferentialAsanaRepresentationField(),

      new DifferentialChangesSinceLastUpdateField(),
      new DifferentialBranchField(),

      new DifferentialBlameRevisionField(),
      new DifferentialPathField(),
      new DifferentialHostField(),
      new DifferentialLintField(),
      new DifferentialUnitField(),
      new DifferentialRevertPlanField(),

      new DifferentialApplyPatchField(),

      new DifferentialRevisionIDField(),

      new DifferentialAffectedServicesField(),
    );

    $default_fields = array();
    foreach ($fields as $field) {
      $default_fields[$field->getFieldKey()] = array(
        'disabled' => $field->shouldDisableByDefault(),
      );
    }

    return array(
      $this->newOption(
        'differential.fields',
        $custom_field_type,
        $default_fields)
        ->setCustomData(
          id(new DifferentialRevision())->getCustomFieldBaseClass())
        ->setDescription(
          pht(
            "Select and reorder revision fields.\n\n".
            "NOTE: This feature is under active development and subject ".
            "to change.")),
      $this->newOption(
        'differential.whitespace-matters',
        'list<regex>',
        array(
          '/\.py$/',
          '/\.l?hs$/',
        ))
        ->setDescription(
          pht(
            "List of file regexps where whitespace is meaningful and should ".
            "not use 'ignore-all' by default")),
      $this->newOption('differential.require-test-plan-field', 'bool', true)
        ->setBoolOptions(
          array(
            pht("Require 'Test Plan' field"),
            pht("Make 'Test Plan' field optional"),
          ))
        ->setSummary(pht('Require "Test Plan" field?'))
        ->setDescription(
          pht(
            "Differential has a required 'Test Plan' field by default. You ".
            "can make it optional by setting this to false. You can also ".
            "completely remove it above, if you prefer.")),
      $this->newOption('differential.enable-email-accept', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Enable Email "!accept" Action'),
            pht('Disable Email "!accept" Action'),
          ))
        ->setSummary(pht('Enable or disable "!accept" action via email.'))
        ->setDescription(
          pht(
            'If inbound email is configured, users can interact with '.
            'revisions by using "!actions" in email replies (for example, '.
            '"!resign" or "!rethink"). However, by default, users may not '.
            '"!accept" revisions via email: email authentication can be '.
            'configured to be very weak, and email "!accept" is kind of '.
            'sketchy and implies the revision may not actually be receiving '.
            'thorough review. You can enable "!accept" by setting this '.
            'option to true.')),
      $this->newOption('differential.generated-paths', 'list<regex>', array())
        ->setSummary(pht('File regexps to treat as automatically generated.'))
        ->setDescription(
          pht(
            'List of file regexps that should be treated as if they are '.
            'generated by an automatic process, and thus be hidden by '.
            'default in Differential.'.
            "\n\n".
            'NOTE: This property is cached, so you will need to purge the '.
            'cache after making changes if you want the new configuration '.
            'to affect existing revisions. For instructions, see '.
            '**[[ %s | Managing Caches ]]** in the documentation.',
            $caches_href))
        ->addExample("/config\.h$/\n#/autobuilt/#", pht('Valid Setting')),
      $this->newOption('differential.sticky-accept', 'bool', true)
        ->setBoolOptions(
          array(
            pht('Accepts persist across updates'),
            pht('Accepts are reset by updates'),
          ))
        ->setSummary(
          pht('Should "Accepted" revisions remain "Accepted" after updates?'))
        ->setDescription(
          pht(
            'Normally, when revisions that have been "Accepted" are updated, '.
            'they remain "Accepted". This allows reviewers to suggest minor '.
            'alterations when accepting, and encourages authors to update '.
            'if they make minor changes in response to this feedback.'.
            "\n\n".
            'If you want updates to always require re-review, you can disable '.
            'the "stickiness" of the "Accepted" status with this option. '.
            'This may make the process for minor changes much more burdensome '.
            'to both authors and reviewers.')),
      $this->newOption('differential.allow-self-accept', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Allow self-accept'),
            pht('Disallow self-accept'),
          ))
        ->setSummary(pht('Allows users to accept their own revisions.'))
        ->setDescription(
          pht(
            "If you set this to true, users can accept their own revisions. ".
            "This action is disabled by default because it's most likely not ".
            "a behavior you want, but it proves useful if you are working ".
            "alone on a project and want to make use of all of ".
            "differential's features.")),
      $this->newOption('differential.always-allow-close', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Allow any user'),
            pht('Restrict to submitter'),
          ))
        ->setSummary(pht('Allows any user to close accepted revisions.'))
        ->setDescription(
          pht(
            'If you set this to true, any user can close any revision so '.
            'long as it has been accepted. This can be useful depending on '.
            'your development model. For example, github-style pull requests '.
            'where the reviewer is often the actual committer can benefit '.
            'from turning this option to true. If false, only the submitter '.
            'can close a revision.')),
      $this->newOption('differential.always-allow-abandon', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Allow any user'),
            pht('Restrict to submitter'),
          ))
        ->setSummary(pht('Allows any user to abandon revisions.'))
        ->setDescription(
          pht(
            'If you set this to true, any user can abandon any revision. If '.
            'false, only the submitter can abandon a revision.')),
      $this->newOption('differential.allow-reopen', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Enable reopen'),
            pht('Disable reopen'),
          ))
        ->setSummary(pht('Allows any user to reopen a closed revision.'))
        ->setDescription(
          pht(
            'If you set this to true, any user can reopen a revision so '.
            'long as it has been closed. This can be useful if a revision '.
            'is accidentally closed or if a developer changes his or her '.
            'mind after closing a revision. If it is false, reopening '.
            'is not allowed.')),
      $this->newOption('differential.close-on-accept', 'bool', false)
        ->setBoolOptions(
          array(
            pht('Treat Accepted Revisions as "Closed"'),
            pht('Treat Accepted Revisions as "Open"'),
          ))
        ->setSummary(pht('Allows "Accepted" to act as a closed status.'))
        ->setDescription(
          pht(
            'Normally, Differential revisions remain on the dashboard when '.
            'they are "Accepted", and the author then commits the changes '.
            'to "Close" the revision and move it off the dashboard.'.
            "\n\n".
            'If you have an unusual workflow where Differential is used for '.
            'post-commit review (normally called "Audit", elsewhere in '.
            'Phabricator), you can set this flag to treat the "Accepted" '.
            'state as a "Closed" state and end the review workflow early.'.
            "\n\n".
            'This sort of workflow is very unusual. Very few installs should '.
            'need to change this option.')),
      $this->newOption('differential.days-fresh', 'int', 1)
        ->setSummary(
          pht(
            "For how many business days should a revision be considered ".
            "'fresh'?"))
        ->setDescription(
          pht(
            'Revisions newer than this number of days are marked as fresh in '.
            'Action Required and Revisions Waiting on You views. Only work '.
            'days (not weekends and holidays) are included. Set to 0 to '.
            'disable this feature.')),
      $this->newOption('differential.days-stale', 'int', 3)
        ->setSummary(
          pht("After this many days, a revision will be considered 'stale'."))
        ->setDescription(
          pht(
            "Similar to `%s` but marks stale revisions. ".
            "If the revision is even older than it is when marked as 'old'.",
            'differential.days-fresh')),
      $this->newOption(
        'metamta.differential.subject-prefix',
        'string',
        '[Differential]')
        ->setDescription(pht('Subject prefix for Differential mail.')),
      $this->newOption(
        'metamta.differential.attach-patches',
        'bool',
        false)
        ->setBoolOptions(
          array(
            pht('Attach Patches'),
            pht('Do Not Attach Patches'),
          ))
        ->setSummary(pht('Attach patches to email, as text attachments.'))
        ->setDescription(
          pht(
            'If you set this to true, Phabricator will attach patches to '.
            'Differential mail (as text attachments). This will not work if '.
            'you are using SendGrid as your mail adapter.')),
      $this->newOption(
        'metamta.differential.inline-patches',
        'int',
        0)
        ->setSummary(pht('Inline patches in email, as body text.'))
        ->setDescription(
          pht(
            "To include patches inline in email bodies, set this to a ".
            "positive integer. Patches will be inlined if they are at most ".
            "that many lines. For instance, a value of 100 means 'inline ".
            "patches if they are no longer than 100 lines'. By default, ".
            "patches are not inlined.")),
      // TODO: Implement 'enum'? Options are 'unified' or 'git'.
      $this->newOption(
        'metamta.differential.patch-format',
        'string',
        'unified')
        ->setDescription(
          pht("Format for inlined or attached patches: 'git' or 'unified'.")),
      $this->newOption(
        'metamta.differential.unified-comment-context',
        'bool',
        false)
        ->setBoolOptions(
          array(
            pht('Show context'),
            pht('Do not show context'),
          ))
        ->setSummary(pht('Show diff context around inline comments in email.'))
        ->setDescription(
          pht(
            'Normally, inline comments in emails are shown with a file and '.
            'line but without any diff context. Enabling this option adds '.
            'diff context and the comment thread.')),
    );
  }

}
