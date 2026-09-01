<?php

declare(strict_types = 1);

use CRM_Mailtrail_ExtensionUtil as E;

// Activity custom group: `recorded` marks every activity this extension wrote
// (what a retention policy selects on), `template_id` is the message template
// the mail was rendered from (what Recorder::sentSince() filters on).
$field = static fn (string $name, string $label, string $dataType, string $htmlType, int $weight): array => [
  'name' => 'CustomField__mailtrail_' . $name,
  'entity' => 'CustomField',
  'cleanup' => 'always',
  'update' => 'unmodified',
  'params' => [
    'version' => 4,
    'values' => [
      'custom_group_id.name' => 'mailtrail',
      'name' => $name,
      'label' => $label,
      'data_type' => $dataType,
      'html_type' => $htmlType,
      'is_searchable' => TRUE,
      'is_active' => TRUE,
      'column_name' => $name,
      'weight' => $weight,
    ],
    'match' => ['custom_group_id', 'name'],
  ],
];

return [
  [
    'name' => 'CustomGroup__mailtrail',
    'entity' => 'CustomGroup',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'mailtrail',
        'title' => E::ts('Mail Trail'),
        'extends' => 'Activity',
        'style' => 'Inline',
        'is_active' => TRUE,
        'is_public' => FALSE,
        'collapse_display' => TRUE,
        'weight' => 100,
      ],
      'match' => ['name'],
    ],
  ],
  $field('recorded', E::ts('Recorded by Mail Trail'), 'Boolean', 'Radio', 1),
  $field('template_id', E::ts('Message template ID'), 'Int', 'Text', 2),
];
