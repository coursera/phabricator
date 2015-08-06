<?php

/**
 * Custom field for Coursera to specify backend services
 * that are affected by a given diff.
 *
 * We use the value of this field to determine which services
 * should be [optionally] deployed after a diff is landed.
 *
 * Future improvements:
 *   - Validate service names against a list of known services
 *   - Determine which services are affected by parsing changed files (in Arcanist)
 */
final class DifferentialAffectedServicesField
  extends DifferentialStoredCustomField {

  private $error;

  public function getFieldKey() {
    return 'phabricator:coursera-services';
  }

  public function getFieldKeyForConduit() {
    return 'courseraServices';
  }

  public function isFieldEnabled() {
    return true;
  }

  public function canDisableField() {
    return false;
  }

  public function getValueForStorage() {
    return json_encode($this->getValue());
  }

  public function setValueFromStorage($value) {
    try {
      $this->setValue(phutil_json_decode($value));
    } catch (PhutilJSONParserException $ex) {
      $this->setValue(array());
    }
    return $this;
  }

  public function getFieldName() {
    return pht('Affected Services');
  }

  public function getFieldDescription() {
    return pht('Lists services affected by this diff.');
  }

  public function shouldAppearInPropertyView() {
    return true;
  }

  public function renderPropertyViewLabel() {
    return $this->getFieldName();
  }

  public function renderPropertyViewValue(array $handles) {
    return phutil_implode_html(phutil_tag('br'), $this->getValue());
  }

  public function shouldAppearInEditView() {
    return true;
  }

  public function shouldAppearInApplicationTransactions() {
    return true;
  }

  public function readValueFromRequest(AphrontRequest $request) {
    $this->setValue($request->getStrList($this->getFieldKey()));
    return $this;
  }

  public function renderEditControl(array $handles) {
    return id(new AphrontFormTextControl())
      ->setLabel(pht('Affected services'))
      ->setCaption(
        pht('Example: %s', phutil_tag('tt', array(), 'catalog, courservice')))
      ->setName($this->getFieldKey())
      ->setValue(implode(', ', nonempty($this->getValue(), array())))
      ->setError($this->error);
  }

  public function getOldValueForApplicationTransactions() {
    return array_unique(nonempty($this->getValue(), array()));
  }

  public function getNewValueForApplicationTransactions() {
    return array_unique(nonempty($this->getValue(), array()));
  }

  public function validateApplicationTransactions(
    PhabricatorApplicationTransactionEditor $editor,
    $type,
    array $xactions) {

    $this->error = null;

    $errors = parent::validateApplicationTransactions(
      $editor,
      $type,
      $xactions);

    $transaction = null;
    foreach ($xactions as $xaction) {
      $old = $xaction->getOldValue();
      $new = $xaction->getNewValue();

      $add = array_diff($new, $old);
      if (!$add) {
        continue;
      }
    }

    return $errors;
  }

  public function getApplicationTransactionTitle(
    PhabricatorApplicationTransaction $xaction) {

    $old = $xaction->getOldValue();
    if (!is_array($old)) {
      $old = array();
    }

    $new = $xaction->getNewValue();
    if (!is_array($new)) {
      $new = array();
    }

    $add = array_diff($new, $old);
    $rem = array_diff($old, $new);

    $author_phid = $xaction->getAuthorPHID();
    if ($add && $rem) {
      return pht(
        '%s updated affected service(s): added %d %s; removed %d %s.',
        $xaction->renderHandleLink($author_phid),
        new PhutilNumber(count($add)),
        implode(', ', $add),
        new PhutilNumber(count($rem)),
        implode(', ', $rem));
    } else if ($add) {
      return pht(
        '%s added %d affected service(s): %s.',
        $xaction->renderHandleLink($author_phid),
        new PhutilNumber(count($add)),
        implode(', ', $add));
    } else if ($rem) {
      return pht(
        '%s removed %d affected service(s): %s.',
        $xaction->renderHandleLink($author_phid),
        new PhutilNumber(count($rem)),
        implode(', ', $rem));
    }

    return parent::getApplicationTransactionTitle($xaction);
  }

  public function shouldAppearInCommitMessage() {
    return true;
  }

  public function shouldAppearInCommitMessageTemplate($revision) {
    return true;
  }

  public function getCommitMessageLabels() {
    return array(
      'Services',
      'Affected services',
      'Affected service',
    );
  }

  public function parseValueFromCommitMessage($value) {
    return preg_split('/[\s,]+/', $value, $limit = -1, PREG_SPLIT_NO_EMPTY);
  }

  public function readValueFromCommitMessage($value) {
    $this->setValue($value);
    return $this;
  }

  public function renderCommitMessageValue(array $handles) {
    $value = $this->getValue();
    if (!$value) {
      return null;
    }
    return implode(', ', $value);
  }

  public function shouldAppearInConduitDictionary() {
    return true;
  }


}

