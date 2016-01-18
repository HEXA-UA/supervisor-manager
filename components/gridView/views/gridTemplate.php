<div class="box lteGridTemplate">
    <?php if ($this->context->tableTitle):?>
        <div class="box-header">
            <h3 class="box-title"><?php echo $this->context->tableTitle;?></h3>
            <div class="box-tools pull-right"></div>
        </div>
    <?php endif;?>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="dataTables_wrapper dt-bootstrap">
            <div class="row">
                <div class="col-sm-6"></div>
                <div class="col-sm-6"></div>
            </div>
            <?php echo $this->context->beforeItems ?>
            <br>
            <div class="row">
                <div class="col-sm-12">{items}</div>
            </div>
            <div class="row options">
                <div class="col-sm-8 paginationBlock">
                    <div class="dataTables_paginate paging_simple_numbers">{pager}</div>
                </div>
                <div class="col-sm-4 summary">
                    <div class="dataTables_info" role="status" aria-live="polite">{summary}</div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
