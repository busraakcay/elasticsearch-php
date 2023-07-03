<div class="container">
    <div class="card border-0" style="width: 100%">
        <div class="card-body">
            <div class="row justify-content-between align-items-center px-4">
                <h5 class="card-title"><?php echo $advertDetail['name']; ?></h5>
                <div>
                    <?php echo '<a class="btn btn-primary" href="edit' . '?params=' . $id . '">'; ?>İlanı Düzenle </a>
                    <?php echo '<a class="btn btn-danger" href="delete' . '?params=' . $id . '">'; ?>Sil</a>
                </div>
            </div>
            <hr>
            <div class="px-5">
                <small class="card-title"><?php echo $advertDetail['company_name']; ?></small><br>
                <small class="card-text text-secondary"><?php echo $advertDetail['type'] === "veriliyor" ? "Satılık" : "Kiralık" ?>-<?php echo $advertDetail['status'] === "1" ? "Sıfır" : "Kiralık"; ?></small>
                <br>
                <small class="card-text text-secondary"><?php echo $advertDetail['categories'][0]["category_name"] . " > " . $advertDetail['categories'][0]["parent_name"] ; ?></small>
                <br><br>
                <p class="card-text"><?php echo $advertDetail['warehouse']; ?></p>
                <br><br>
                <p class="card-text text-right font-weight-bold text-danger"><?php echo $advertDetail['price'] . " " . $advertDetail['currency']; ?></p>
            </div>

        </div>
    </div>
</div>