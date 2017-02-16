
    <!-- Feedback form -->
    <form class="form-horizontal" role="form" method="post" action="<?php if ($issue['ref']) {echo $issue['ref'];} ?>">
        <div class="panel panel-default">
	    <div class="panel-heading text-right">		 
	    	<div class="text-left">
		    <b><?=isset($id) && !empty($id) ? 'Endre tilbakemelding' : 'Ny tilbakemelding'?></b> 
	    	    <div class="pull-right text-right">		 
		        <b>Status</b> <span class="label label-primary"><?=isset($status['name']) ? $status['name'] : 'Ny' ?></span>&nbsp&nbsp<b>Ansvarlig</b> <span class="label label-primary"><?=($assigned['full_name_display'] ? $assigned['full_name_display'] : 'Ikke tildelt')?></span>
		    </div>
                </div>	
	    </div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Emne</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="subject" name="subject"
                               placeholder="Kort beskrivende tekst"
                               value="<?php if (isset($subject)) { echo $subject; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errSubject)) {
                            echo "<p class='text-danger'>$errSubject</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="type" class="col-sm-2 control-label">Type</label>

                    <div class="col-sm-4">
                        <select id="type" name="type" class="form-control" <? if(!$auth) { echo "disabled"; }?>>
                        <?php foreach($types as $item) { ?>
<option value="<?=$item['id']?>" <?php if ($type === $item['id']) { echo("selected");}?>> <?php echo $item['name']; ?></option>
                        <?php } ?>
                        </select>
                        <?php if (isset($errType)) {
                            echo "<p class='text-danger'>$errType</p>";
                        } ?>
                    </div>
                    <label for="level" class="col-sm-2 control-label">Alvorlighetsgrad</label>

                    <div class="col-sm-4">
                        <select id="level" name="level" class="form-control" <? if(!$auth) { echo "disabled"; }?>>
                            <?php foreach($levels as $item) { ?>
<option value="<?=$item['id']?>" <?php if ($level === $item['id']) { echo("selected");}?>> <?php echo $item['name']; ?></option>
                            <?php } ?>
                        </select>
                        <?php if (isset($errLevel)) {
                            echo "<p class='text-danger'>$errLevel</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description" class="col-sm-2 control-label">Beskrivelse</label>

                    <div class="col-sm-10">
                        <textarea class="form-control" rows="4" id="description" name="description" <? if(!$auth) { echo "disabled"; }?>><?php if (isset($description)) { echo $description; }?></textarea>
                        <?php if (isset($errDesc)) {
                            echo "<p class='text-danger'>$errDesc</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-2 control-label">Navn</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="name" name="name" placeholder="Ditt navn"
                               value="<?php if (isset($name)) { echo $name; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errName)) {
                            echo "<p class='text-danger'>$errName</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-sm-2 control-label">Email</label>

                    <div class="col-sm-10">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="example@domain.com" value="<?php if (isset($email)) { echo $email; }?>"
			       <? if(!$auth) { echo "disabled"; }?>>
                        <?php if (isset($errEmail)) {
                            echo "<p class='text-danger'>$errEmail</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="human" class="col-sm-2 control-label">Er du en robot?</label>

                    <div class="col-sm-10">
			<div id="g-recaptcha" class="g-recaptcha" data-size="normal" data-sitekey="<?=RECAPTCHA_SITE_KEY?>"></div>
                        <?php if (isset($errHuman)) {
                            echo "<p class='text-danger'>$errHuman</p>";
                        } ?>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-12">
                        <?php echo $result; ?>
                    </div>
                </div>
                <? if(!empty($comments )) { ?>
		<div class="form-group">
			<label for="comments" class="col-sm-2 control-label">Comments</label>
                	<div id="comments" class="col-sm-10">
                <? foreach($comments as $comment) { ?>
				<div class ="list-group-item">
					<div class="list-group-heading"><b><?=$comment['user'];?></b> <span class="label label-default pull-right"><?=date('d M Y H:i', strtotime($comment['created']));?></span></div><br/>
					<div class="list-group-item-text"><?=$comment['html'];?></div>
				</div>
		<? } ?>
			</div>
                </div>
                <? } ?>

            </div>
            <div class="panel-footer">
                <div class="form-group">
                    <div class="col-sm-12 text-left">
                        <div class="text-right">
                            <a href="/feedback/new" class="btn btn-default" role="button">Ny tilbakemelding</a>
                            <input id="submit" name="submit" type="submit" value="Send" class="btn btn-primary">
			</div>
                    </div>
                </div>

            </div>
    </form>

