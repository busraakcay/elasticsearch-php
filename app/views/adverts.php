<div class="row">
    <div class="col-md-3">
        <div class="card-body p-0">
            <div class="container">
                <h5 class="mb-3"><?php echo $categoryName ?></h5>
                <ul class="list-group">
                    <?php foreach ($categories as $category) : ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php
                            if ($categoryName === "Kategoriler") {
                                echo '<a class="text-decoration-none text-dark" href="categories' . '?params=' . $category["key"] . '">';
                            } else {
                                $textColor = "text-secondary";
                                if (isset($subCategoryName) && $category["key"] === $subCategoryName) {
                                    $textColor = "text-danger";
                                }
                                echo '<a class="text-decoration-none ' . $textColor . '" href="subcategories' . '?params=' . $category["key"] . '&subOf=' . $categoryName . '">';
                            }
                            ?>
                            <small><?php echo $category['key']; ?></small></a>
                            <span class="badge badge-primary badge-pill ml-1"><?php echo $category['doc_count']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9 p-0">
        <div class="card border-0 p-0">
            <div class="card-body p-0 m-0">
                <div class="row justify-content-center">
                    <?php foreach ($adverts as $advert) : ?>
                        <div class="card col-2 m-2 d-flex align-items-stretch" style="width: 18rem;">
                            <!-- <div class="card-body row text-center justify-content-center align-items-center">
                <img src="<?php echo $advert["_source"]['images'][0]["url"]; ?>" alt="<?php echo $advert["_source"]['id']; ?>" class="card-img-top img-fluid m-1">
            </div> -->
                            <div class="card-body row text-center justify-content-center align-items-center">
                                <?php echo '<a href="show' . '?params=' . $advert["_source"]['id'] . '">'; ?>
                                <p class="card-title font-weight-bold"> <?php echo $advert["_source"]["name"] ?> </p>
                            </div></a>
                            <div class="card-footer bg-white text-center">
                                <p class="card-text text-secondary"><?php echo $advert["_source"]["type"] ?> - <?php echo $advert["_source"]["status"] ?></p>
                                <p class="card-text font-weight-bold text-danger"><?php echo $advert["_source"]["beautifiedprice"] ?></p>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <br>
        <?php if (isset($scrollId)) : ?>
            <div class="row justify-content-center">
                <?php echo '<a class="btn btn-primary bg-primary d-block col-3" href="index' . '?params=' . $scrollId . ' ">'; ?>Daha Fazla Görüntüle</a>
            </div>
        <?php endif ?>
    </div>
</div>