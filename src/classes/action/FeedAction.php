<?php

namespace iutnc\touiteur\action;

use iutnc\touiteur\auth\Auth;
use iutnc\touiteur\auth\AuthException;

require_once "vendor/autoload.php";

class FeedAction extends Action
{

    public function execute(): string
    {
        $html = "";
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $html .= '<div class="tweet">
        <span id="titleTweet">
            <div class="author">John Doe</div>
            <div class="actions" id="follow"><button>Suivre</button></div>
        </span>
    <div class="timestamp">2 hours ago</div>
    <div class="content">
        This is a sample tweet on Touiteur.app.
    </div>
    <div class="tags">
    <p class="trending">#Populaire <p id="numberTweet" class="trending">12k</p></p>
    <p>#Exemple <p id="numberTweet">1k</p></p>    
    </div>
    <div class="actions">
        <button>Like</button>
        <button>Dislike</button>
        <button>Retouite</button>
    </div>
</div>
    <a id="postTweet" href="?action=postTweet">
    <img src="images/postTweet.png" alt="post a tweet"/>
    </a>';
        }
        return $html;
    }
}