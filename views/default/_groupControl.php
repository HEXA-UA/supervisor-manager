<!--<div class="margin">-->
<div class="btn-group groupControl" data-group-name="<?php echo $groupName ?>">
    <button type="button" class="btn btn-default">Group Options</button>
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li><a href="#" data-action="startProcessGroup"><i class="fa fa-play"></i> Start all</a></li>
        <li class="divider"></li>
        <li><a href="#" data-action="stopProcessGroup"><i class="fa fa-stop"></i> Stop all</a></li>
        <li class="divider"></li>
        <li><a href="#" class="processConfigControl" data-action="addNewGroupProcess"><i class="fa fa-plus"></i> Create new process</a></a></li>
        <li class="divider"></li>
        <li><a href="#" class="processConfigControl" data-action="deleteGroupProcess"><i class="fa fa-remove"></i> Remove process</a></li>
        <li class="divider"></li>
        <li><a href="#" class="processConfigControl" data-action="deleteProcess"><i class="fa fa-minus-square"></i> Remove group</a></li>
    </ul>
</div>