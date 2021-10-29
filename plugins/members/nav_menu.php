<?php

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

if (!$members->is_user()) {
    $helper->redirect('index.php?plugin=members');
}

$member_pages = $pages->list_items('', '', 1, '', '', 1);

?>
<div class="d-block d-lg-none">
    <div>
        <button class="btn bg-light p-2 text-ellipsis profile-img-left" type="button" data-toggle="collapse" data-target="#collapse-1" aria-expanded="true" aria-controls="collapse-1">
            <div class="row">
                <div class="col-xl-5">
                    <img src="<?=_SITE_PATH . '/uploads/avatar/' . md5((string) $_SESSION['user_info']['id']) . '/' . $_SESSION['user_info']['avatar']?>" alt="" class="rounded-circle" />
                </div>
                <div class="col-xl-7 pt-2">
                    Hi, <?=$_SESSION['user_info']['first_name']?> <i class="icon-angle-down"></i>
                </div>
            </div>
            <div id="collapse-1" class="collapse">
                <div class="row">
                    <div class="col-12 mt-4">
                        <div class="text-right card-body bg-light text-center pt-0 " style="font-size: 16px">
                            <a href="index.php?plugin=members&amp;op=logout" title="<?=_LOGOUT?>">
                                <span class="icon-power-off"></span> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </button>
    </div>
</div>
<div class="separator xs"></div>
<h4 role="heading">Member Panel</h4>
<div class="nav flex-column nav-pills " id="v-pills-tab" role="tablist" aria-orientation="vertical" >
    <a class="nav-link <?=(empty($_REQUEST['file'])) ? 'active' : ''?>" id="v-pills-home-account"  href="members/dashboard" title="<?=_MY_ACCOUNT?>">
        <span class="icon-home icon-lg icon-fw"></span> <?=_MY_ACCOUNT?>
    </a>
    <a class="nav-link <?=($_REQUEST['file'] == 'profile') ? 'active' : ''?>" id="v-pills-profile-tab"  href="index.php?plugin=members&amp;file=profile" title="<?=_EDIT?> <?=_PROFILE?>">
        <span class="icon-user icon-lg icon-fw"></span> <?=_PROFILE?>
    </a>
    <?php if ($_SESSION['user_info']['grouped'] != 4) {?>
    <a class="nav-link <?=($_REQUEST['file'] == 'deals') ? 'active' : ''?>" id="v-pills-deals-tab"  href="members/deals" title="Deals">
        <span class="icon-tags icon-lg icon-fw"></span> Deals
    </a>
    <a class="nav-link <?=($_REQUEST['file'] == 'events') ? 'active' : ''?>" id="v-pills-events-tab" href="members/events" title="Events">
        <span class="icon-calendar icon-lg icon-fw"></span> Events
    </a>


    <a class="nav-link <?=($_REQUEST['file'] == 'articles') ? 'active' : ''?>" id="v-pills-articles-tab" href="members/articles" title="Articles">
        <span class="icon-pencil icon-lg icon-fw"></span> My Articles
    </a>
<!--     <a class="nav-link <?=($_REQUEST['file'] == 'notifications') ? 'active' : ''?>"" id="v-pills-notifications-tab" href="members/notifications" title="Notifications">
        <span class="icon-exclamation-triangle icon-lg icon-fw"></span> Notifications <i class="icon-info-circle" data-toggle="tooltip" data-placement="top" title="Need links"></i>
    </a> -->
    <?php }?>
    <a class="nav-link <?=($_REQUEST['file'] == 'wishlist') ? 'active' : ''?>" id="v-pills-favorites-tab"  title="Favorites" href="members/wishlist">
        <span class="icon-heart icon-lg icon-fw"></span> My Favorites   
    </a>
    <?php if ($_SESSION['user_info']['grouped'] != 4) {?>
     <a class="nav-link <?=($_REQUEST['file'] == 'stats') ? 'active' : ''?>" id="v-pills-analytics-tab"  title="Statistics" href="members/stats">
        <span class="icon-signal icon-lg icon-fw"></span> Statistics
    </a>
<!--    <a class="nav-link " id="v-pills-payments-tab"  title="Payments" href="#">-->
<!--        <span class="icon-bank icon-lg icon-fw"></span> Payments  <i class="icon-info-circle" data-toggle="tooltip" data-placement="top" title="Need links"></i>-->
<!--    </a>  -->
    <?php }?>

    <a class="nav-link inline" id="v-pills-support-tab"  title="Support"  href='#preview'>
        <span class="icon-question icon-lg icon-fw"></span> Support 
    </a>     
    <a class="nav-link " id="v-pills-logoff-tab"  href="index.php?plugin=members&amp;op=logout" title="<?=_LOGOUT?>">
        <span class="icon-power-off icon-lg icon-fw"></span> Logout
    </a>
</div>

<div class="separator "></div>


<div id="preview" class="p-4 d-none pb-5">
    <h4 class="modal-title">Email Support</h4>
    <form action="sendMail.php" method="post" >
        <fieldset class="form-group">
            <label for="exampleInputEmail1">Subject</label>
            <input type="text" class="form-control" name="subject" placeholder="Subject">
        </fieldset>
        <fieldset class="form-group">
            <label for="exampleTextarea">Message</label>
            <textarea class="form-control" name="message" rows="3"></textarea>
        </fieldset>
        <button type="submit" class="btn btn-primary mb-2">Submit</button>                    
        <input type="hidden" name="name" value="<?=$_SESSION['user_info']['full_name']?>" />
        <input type="hidden" name="email" value="<?=$_SESSION['user_info']['email']?>" />
        <input type="hidden" name="source" value="support" />
        <input type="hidden" name="reply-to" value="<?=$_SESSION['user_info']['email']?>" />
    </form>
    <div class="separator"></div>
</div>

