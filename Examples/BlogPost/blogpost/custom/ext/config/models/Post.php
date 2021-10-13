<?php return array (
  'table' => 'posts',
  'modeldata' =>
  array (
    'config' =>
    array (
      'type' => 'KModel',
    ),
    'schema' =>
    array (
      0 =>
      array (
        0 => 'id',
        1 => 'bigInteger',
        2 => 'text',
        3 => 'ID',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '11|0|1|0',
        11 => '',
        12 => 'index',
        13 => 'IColumn',
      ),
      1 =>
      array (
        0 => 'created_at',
        1 => 'timestamp',
        2 => 'datetime',
        3 => 'CreateDate',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '00|0|1|0',
        11 => '',
        12 => 'index',
        13 => 'IColumn',
      ),
      2 =>
      array (
        0 => 'updated_at',
        1 => 'timestamp',
        2 => 'datetime',
        3 => 'UpdateDate',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '00|0|1|0',
        11 => '',
        12 => 'index',
        13 => 'IColumn',
      ),
      3 =>
      array (
        0 => 'deleted_at',
        1 => 'timestamp',
        2 => 'datetime',
        3 => 'DeletedDate',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '00|0|1|0',
        11 => '',
        12 => 'index',
        13 => 'IColumn',
      ),
      4 =>
      array (
        0 => 'data',
        1 => 'string',
        2 => 'json',
        3 => 'Data',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '00|0|0|0',
        11 => '',
        12 => '',
        13 => 'IColumn',
      ),
      5 =>
      array (
        0 => 'igroup_id',
        1 => 'unsignedBigInteger',
        2 => 'rel-b1',
        3 => 'Group',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '00|0|1|0',
        11 => '',
        12 => 'foreign',
        13 => 'IColumn',
      ),
      6 =>
      array (
        0 => 'intro',
        1 => 'string',
        2 => 'text',
        3 => 'Name',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '11|1|1|1',
        11 => '',
        12 => '',
        13 => 'Column',
      ),
      7 =>
      array (
        0 => 'title',
        1 => 'string|500',
        2 => 'text',
        3 => 'Title',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '11|1|1|1',
        11 => '',
        12 => '',
        13 => 'Column',
      ),
      8 =>
      array (
        0 => 'body',
        1 => 'string|15000',
        2 => 'text',
        3 => 'Body',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '11|1|1|1',
        11 => '',
        12 => '',
        13 => 'Column',
      ),
      9 =>
      array (
        0 => 'user_id',
        1 => 'unsignedBigInteger',
        2 => 'rel-b1',
        3 => 'User',
        4 => '',
        5 => '',
        6 => '',
        7 => '0|12|1',
        8 => '',
        9 => '',
        10 => '11|1|1|1',
        11 => '',
        12 => 'foreign',
        13 => 'Column',
      ),
    ),
    'relationships' =>
    array (
      'igroup_id' =>
      array (
        0 => 'igroup',
        1 => 'Group',
        2 => 'igroup_id,id',
        3 => '{{name}}',
        4 =>
        array (
        ),
      ),
      'user_id' =>
      array (
        0 => 'user',
        1 => 'User',
        2 => 'user_id,id',
        3 => '{{id}}',
        4 =>
        array (
        ),
      ),
    ),
    'forms' =>
    array (
      'index' =>
      array (
        0 =>
        array (
          'name' => 'Table',
          'icon' => 'icon-table',
          'photo' => 'photo',
          'status' => 'status',
          'nosearch' =>
          array (
          ),
        ),
        1 =>
        array (
          'name' => 'Card',
          'icon' => 'icon-grid',
          'photo' => 'photo',
          'status' => 'status',
          'nosearch' =>
          array (
          ),
          'cardtitle' => '{{name}}',
          'cardsubtitle' => '{{status}}',
        ),
      ),
      'create' =>
      array (
        0 =>
        array (
          'name' => 'Create',
          'type' => 'tab',
        ),
      ),
      'show' =>
      array (
        0 =>
        array (
          'name' => 'Show',
          'type' => 'tree',
        ),
      ),
      'edit' =>
      array (
        0 =>
        array (
          'name' => 'Edit',
          'type' => 'tab',
        ),
      ),
    ),
    'values' =>
    array (
      'select' =>
      array (
      ),
    ),
    'actions' =>
    array (
    ),
    'triggers' =>
    array (
    ),
    'script' =>
    array (
    ),
  ),
);