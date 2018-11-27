<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 07.11.2018
 * Time: 10:40
 */

class Stats
{
    public $views;

    public $reposts;

    public $comments;

    public $likes;

    public function __construct()
    {
        $this->views = 120;
        $this->reposts = 4;
        $this->comments = 12;
        $this->likes = 5;
    }

    /**
     * @return mixed
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param mixed $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @return mixed
     */
    public function getReposts()
    {
        return $this->reposts;
    }

    /**
     * @param mixed $reposts
     */
    public function setReposts($reposts)
    {
        $this->reposts = $reposts;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param mixed $likes
     */
    public function setLikes($likes)
    {
        $this->likes = $likes;
    }
}