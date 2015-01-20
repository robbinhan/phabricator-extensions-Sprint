<?php

final class SprintBoardTaskCard {

  private $project;
  private $viewer;
  private $task;
  private $owner;
  private $canEdit;
  private $task_node_id;
  private $points;

  public function setViewer(PhabricatorUser $viewer) {
    $this->viewer = $viewer;
    return $this;
  }

  public function setProject ($project) {
    $this->project = $project;
    return $this;
  }

  public function getViewer() {
    return $this->viewer;
  }

  public function setTask(ManiphestTask $task) {
    $this->task = $task;
    return $this;
  }

  public function setNode($task_node_id) {
    $this->task_node_id = $task_node_id;
    return $this;
  }

  public function getTask() {
    return $this->task;
  }

  public function setOwner(PhabricatorObjectHandle $owner = null) {
    $this->owner = $owner;
    return $this;
  }
  public function getOwner() {
    return $this->owner;
  }

  public function setCanEdit($can_edit) {
    $this->canEdit = $can_edit;
    return $this;
  }

  public function getCanEdit() {
    return $this->canEdit;
  }

  public function getUserImage() {
    $ownername = $this->owner->getName();
    $ownerlink = '/p/'.$ownername.'/';
    $image_uri = $this->owner->getImageURI();

    $sigil = 'has-tooltip';
    $meta  = array(
        'tip' => pht($ownername),
        'size' => 200,
        'align' => 'E',);
    $image = id(new PHUIIconView())
        ->addSigil($sigil)
        ->setMetadata($meta)
        ->setHref($ownerlink)
        ->setImage($image_uri)
        ->setHeadSize(PHUIIconView::HEAD_SMALL);

  return $image;
  }

  public function getPointList() {
      $pointslabel = 'Points:';
      $pointsvalue = phutil_tag(
          'dd',
          array(
              'class' => 'phui-card-list-value',
          ),
          array($this->points, ' '));

      $pointskey = phutil_tag(
          'dt',
          array(
              'class' => 'phui-card-list-key',
          ),
          array($pointslabel, ' '));
      return phutil_tag(
          'dl',
          array(
              'class' => 'phui-property-list-container',
          ),
          array(
              $pointskey,
              $pointsvalue,
          ));
    }

  public function getItem() {
    require_celerity_resource('phui-workboard-view-css', 'sprint');

    $query = id(new SprintQuery())
        ->setProject($this->project)
        ->setViewer($this->viewer);
    $task = $this->getTask();
    $owner = $this->getOwner();
    $task_phid = $task->getPHID();
    $can_edit = $this->getCanEdit();
    $this->points = $query->getStoryPointsForTask($task_phid);

    $color_map = ManiphestTaskPriority::getColorMap();
    $bar_color = idx($color_map, $task->getPriority(), 'grey');

    $card = id(new PHUIObjectItemView())
      ->setObjectName('T'.$task->getID())
      ->setHeader($task->getTitle())
      ->setGrippable($can_edit)
      ->setHref('/T'.$task->getID())
      ->addSigil('project-card')
      ->setDisabled($task->isClosed())
      ->setMetadata(
        array(
          'objectPHID' => $task_phid,
          'taskNodeID' => $this->task_node_id,
          'points' => $this->points,
        ))
      ->addAction(
            id(new PHUIListItemView())
                ->setName(pht('Edit'))
                ->setIcon('fa-pencil')
                ->addSigil('edit-project-card')
                ->setHref('/project/sprint/board/task/edit/'.$task->getID()
                    .'/'))
      ->setBarColor($bar_color);

     $card->addAttribute($this->getPointList());

    if ($owner) {
      $card->setImageIcon($this->getUserImage());
    }
    return $card;
  }

}
