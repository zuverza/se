<?php

function mash_up_admin_add_node($queue, $subqueue, $node) {
  if (!nodequeue_check_token($node->nid)) {
    return drupal_goto();
  }
  $id = nodequeue_get_content_id($queue, $node);
  nodequeue_subqueue_add($queue, $subqueue, $id);

  // Provide a response if this is javascript.
  if (!empty($_POST['js'])) {
    if (isset($_GET['tab'])) {
      nodequeue_js_output(t('Remove from queue'),
        url("nodequeue/$queue->qid/remove-node/$subqueue->sqid/$node->nid", array('query' => nodequeue_get_query_string($node->nid, TRUE, array('tab' => '1')))),
        nodequeue_subqueue_size_text($queue->size, $queue->size ? min($subqueue->count, $queue->size) : $subqueue->count, FALSE),
        $subqueue->sqid);
    }
    else {
      nodequeue_js_output(nodequeue_title_substitute($queue->link_remove, $queue, $subqueue),
        url("nodequeue/$queue->qid/remove-node/$subqueue->sqid/$node->nid", array('query' => nodequeue_get_query_string($node->nid, TRUE))));
    }
  }

  // There should always be a destination set for this, so just goto wherever.
  drupal_goto();
}