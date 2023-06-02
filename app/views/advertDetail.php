<div class="container">
    <div class="card border-0" style="width: 100%">
        <!-- <img src="<?php
                        echo ($advertDetail["images"][0]);
                        ?>" class="card-img-top" alt="<?php echo $advertDetail['id']; ?>"> -->
        <div class="card-body">
            <div class="row justify-content-between align-items-center px-4">
                <h5 class="card-title"><?php echo $advertDetail['name']; ?></h5>
                <div>
                    <?php echo '<a class="btn btn-primary" href="edit' . '?params=' . $advertDetail['id'] . '">'; ?>İlanı Düzenle </a>
                    <?php echo '<a class="btn btn-danger" href="delete' . '?params=' . $docId . '">'; ?>Sil</a>
                </div>
            </div>
            <hr>
            <div class="px-5">
                <small class="card-title"><?php echo $advertDetail['company_name']; ?></small><br>
                <small class="card-text text-secondary"><?php echo $advertDetail['type']; ?>-<?php echo $advertDetail['status']; ?></small>
                <br><br>
                <p class="card-text"><?php echo $advertDetail['description']; ?></p>
                <br><br>
                <p class="card-text text-right font-weight-bold text-danger"><?php echo $advertDetail['beautifiedprice']; ?></p>
            </div>

        </div>
    </div>
</div>