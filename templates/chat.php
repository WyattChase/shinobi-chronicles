<?php
/**
 * @var System $system
 * @var User $player
 * @var string $self_link
 */
?>
<?php if(!$system->is_legacy_ajax_request): ?>
<style>
        :root {
            --avatar-size: 60px;
        }

        #chatMessage {
            width: 375px;
            height: 100px;
        }
        #user_data_container {
            display: flex;
            flex-direction: row;
        }
        #user_data_container .avatarContainer {
            max-width: clamp(45px, 100%, var(--avatar-size));
            max-height: var(--avatar-size);
            vertical-align: text-bottom;
            flex-shrink: 0;
        }
        #user_data_container .avatarContainer img {
            max-width: inherit !important;
            max-height: inherit !important;
        }
        #user_data_container .character_info {
            display: block;
            align-self: center;
            flex-grow: 1;
            word-wrap: anywhere;
        }
        #user_data_container .character_info a {
            display: inline-block;
        }
        #user_data_container .character_info p {
            margin: 1px 0 3px;
        }
        #user_data_container .character_info .villageIco {
            max-width:20px;
            max-height:20px;
            vertical-align:text-bottom;
        }
        .small_image {
            max-width:20px;
            max-height:20px;
        }
        .mention {
            background-color: rgba(255, 255, 0, 0.3);
        }
        #meme_modal {
            position: absolute;
            z-index: 1001;
	        top: 3%;
            left: 50%;
            border-radius: 10px;
            background-color: var(--theme-content-bg-color);
            transform: translate(-50%, -50%);
        }
        #meme_box {
            overflow-y: auto;
	        height: 200px;
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
        }
        .meme_select {
            cursor: pointer;
            flex-basis: 40px;
            min-width: 0;
            padding: 4px;
        }
        .meme_select img {
            max-width: 40px !important;
            max-height: 40px !important;
        }
        .hidden {
            display: none !important;
        }
</style>
<script type="text/javascript">
        $(document).ready(function(){
            let Chat = $('#chatMessage');
            let chat_max_length = <?=$chat_max_post_length?>

            Chat.keypress(function( event ) {
                if (event.which === 13 && !event.shiftKey && $('#quickReply').prop('checked')) {
                    $('#chatSubmit').trigger('click');
                }
            });
            Chat.keyup(function (evt) {
                if (this.value.length >= chat_max_length - 20) {
                    let remaining = chat_max_length - this.textLength;
                    $('#remainingCharacters').text('Characters remaining: ' + remaining + ' out of ' + chat_max_length);
                }
                else {
                    $('#remainingCharacters').text('');
                }
            });
            $(document).on("click", ".meme_select", function () {
                Chat.val(Chat.val() + $(this).attr("data-code"));
                $("#meme_modal").addClass("hidden");
                $("#meme_toggle").text("+ Meme");
            });
            $(document).on("click", ".meme_toggle", function (e) {
                $("#meme_modal").toggleClass("hidden");
            });
        });
</script>
<?php if(!isset($_GET['no_refresh'])): ?>
<script type="text/javascript">
            var refreshID;
            $(document).ready(function(){
                refreshID = setInterval(function(){$('#socialPosts').load('<?=$self_link?>&request_type=ajax')}, 3000);
            });
</script>
<?php endif ?>

<div class="submenu">
    <ul class="submenu">
        <?php if(isset($_GET['no_refresh'])): ?>
        <li style="width:100%;">
            <a href="<?=$self_link?>">Turn Auto Chat On</a>
        </li>
        <?php else: ?>
        <li style="width:100%;">
            <a href="<?=$self_link?>&no_refresh=1">Turn Auto Chat Off</a>
        </li>
        <?php endif ?>
    </ul>
</div>
<div class="submenuMargin"></div>

<table id="meme_modal" class="table hidden">
    <tr>
        <th>Memes</th>
    </tr>
    <tr>
        <td>
            <div id="meme_box">
                <?php $memes = $system->getMemes(); ?>
                <?php for ($i = 0; $i < count($memes["codes"]); $i++): ?>
                <div data-code="<?=$memes["codes"][$i]?>" class="meme_select">
                    <?=$memes["images"][$i]?>
                </div>
                <?php endfor; ?>
            </div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center">
            <button class="meme_toggle">Close</button>
        </td>
    </tr>
</table>

<table id="chat_input_table" class="table">
    <tr>
        <th>Post Message</th>
    </tr>
    <tr>
        <td style="text-align: center;">
            <button class="meme_toggle">Meme</button>
            <form action="<?=$self_link?>" method="post">
                <textarea id="chatMessage" name="post" style="minlength='3' maxlength='<?=$chat_max_post_length?>"></textarea><br />
                <input type="checkbox" id="quickReply" name="quick_reply" value="1"
                    <?=($_SESSION['quick_reply'] ? "checked='checked'" : '')?> /> Quick reply<br />
                <span id="remainingCharacters" class="red"></span>
                <br />
                <input id="chatSubmit" name="chat_submit" type="submit" value="Post"/>
            </form>
        </td>
    </tr>
</table>

<div id="socialPosts">
    <?php endif ?>
    <table class="table" style="width: 98%;">
        <tr>
            <th style="width:28%;">Users</th>
            <th style="width:61%;">Message</th>
            <th style="width:10%;">Time</th>
        </tr>
        <?php if(empty($posts)): ?>
        <tr>
            <td colspan="3" style="text-align: center;">No posts!</td>
        </tr>
        <?php else: ?>
        <?php foreach($posts as $post): ?>
        <tr class="chat_msg" style="text-align: center;">
            <td>
                <div id='user_data_container'>
                    <div class="avatarContainer">
                        <img src="<?=$post['avatar']?>" />
                    </div>
                    <div class="character_info">
                        <a href="<?=$system->router->links['members']?>&user=<?=$post['user_name']?>"
                            class="<?=$post['class']?> <?=$post['status_type']?>">
                            <?=$post['user_name']?>
                        </a><br />
                        <p>
                            <img class='villageIco' src="./images/village_icons/<?=strtolower($post['village'])?>.png" alt="<?=$post['village']?> Village"
                                title="<?=$post['village']?> Village" />
                            <?=stripslashes($post['title'])?>
                        </p>
                    </div>
                </div>
                <?php if($post['staff_level']): ?>
                <p class="staffMember" style="background-color: <?=$post['staff_banner']['staffColor']?>">
                    <?=$post['staff_banner']['staffBanner']?>
                </p>
                <?php endif ?>
            </td>
            <td>
                <?=$post['message']?>
            </td>
            <td style="font-style:italic;">
                        <div style="margin-bottom:2px"><?=$post['time_string']?></div>
                        <?php if($player->staff_manager->isModerator()): ?>
                            <?=sprintf("<a class='imageLink' href='{$self_link}&delete=%d'>
                                    <img class='small_image' src='../images/delete_icon.png' />
                                </a>", $post['post_id'])?>
                        <?php endif ?>
                        <?= sprintf("<a class='imageLink' href='{$system->router->links['report']}&report_type="
                            . ReportManager::REPORT_TYPE_CHAT . "&content_id=%d'>
                        <img class='small_image' src='../images/report_icon.png' /></a>", $post['post_id'])?>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php endif ?>
    </table>

    <p style="text-align: center;">
        <?php if($min > 0) :?>
            <a href="<?=$self_link?>&min=<?=$previous?><?=$refresh?>">Previous</a>
        <?php endif ?>
        <?php if($min < $max_id): ?>
            <?php if($min != 0): ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
            <?php endif ?>
            <a href="<?=$self_link?>&min=<?=$next?>&no_refresh=1">Next</a>
        <?php endif ?>
    </p>
<?php if(!$system->is_legacy_ajax_request): ?>
    </div>
<?php endif ?>
