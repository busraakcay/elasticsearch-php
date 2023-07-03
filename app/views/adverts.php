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
                                echo '<a class="text-decoration-none text-dark" href="categories' . '?params=' . $category["key"] . '&parentName=' . $categoryBucketValue . '">';
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
                <div class="row d-flex ml-5 justify-content-start">
                    <a class="btn btn-primary" style="border-radius: 30px;" href="bulkCreate">Toplu İlan Ekle</a>
                    <a class="btn btn-primary ml-2" style="border-radius: 30px;" href="bulkUpdate">Toplu İlan Güncelle</a>
                    <a class="btn btn-primary ml-2" style="border-radius: 30px;" href="bulkDelete">Toplu İlan Sil</a>
                </div>
                <div class="row justify-content-center">
                    <?php foreach ($adverts as $advert) : ?>
                        <div class="card col-2 m-2 d-flex align-items-stretch" style="width: 18rem;">
                            <div class="card-body row text-center justify-content-center align-items-center">
                                <?php echo '<a href="show' . '?params=' . $advert["_id"] . '">'; ?>
                                <p class="card-title font-weight-bold"> <?php echo $advert["_source"]["name"] ?> </p>
                                <?php
                                if (isset($advert["_source"]["is_doping"])) {
                                    if ($advert["_source"]["is_doping"] == 1) {
                                        echo "<small>Doping ilan</small>";
                                    } else {
                                        echo "<small>Normal ilan</small>";
                                    }
                                }
                                ?>
                            </div>
                            </a>
                            <div class="card-footer bg-white text-center">
                                <p class="card-text text-secondary"><?php

                                                                    if ($advert["_source"]["type"] === "veriliyor") {
                                                                        echo "Satılık";
                                                                    }
                                                                    if ($advert["_source"]["type"] === "kiralik") {
                                                                        echo "Kiralık";
                                                                    }
                                                                    if ($advert["_source"]["type"] === "araniyor") {
                                                                        echo "Aranıyor";
                                                                    }
                                                                    ?> - <?php
                                                                            if ($advert["_source"]["status"] === "1") {
                                                                                echo "Sıfır";
                                                                            } else {
                                                                                echo "İkinci El";
                                                                            }
                                                                            ?></p>
                                <p class="card-text font-weight-bold text-danger"><?php echo $advert["_source"]["price"] . " " . $advert["_source"]["currency"] ?></p>
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