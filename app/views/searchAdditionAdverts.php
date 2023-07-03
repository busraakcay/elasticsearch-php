<div class="row justify-content-center align-items-center">
    <?php foreach ($adverts as $advert) : ?>
        <div class="card bg-primary col-11 row mx-0 mb-2">
            <div class="card-body row text-center justify-content-between align-items-center">
                <div>
                    <?php echo '<a href="show' . '?params=' . $advert["_id"] . '">'; ?>
                    <div class="row text-light justify-content-between align-items-center">
                        <small class="d-block font-weight-bold"> <?php echo $advert["_source"]["name"] . " " . $advert["_source"]["updated_at"] ?> </small>
                        <div class="ml-2">(
                            <?php
                            if (isset($advert["_source"]["is_doping"])) {
                                if ($advert["_source"]["is_doping"] == 1) {
                                    echo "<small>Doping ilan</small>";
                                } else {
                                    echo "<small>Normal ilan</small>";
                                }
                            } else {
                                echo "<small>Bilgi yok</small>";
                            }
                            ?>
                            )
                        </div>
                    </div>
                </div>
                <div>
                    <small class="d-block card-text text-light"><?php echo $advert["_source"]["company_name"] ?></small>
                </div>

            </div>
            </a>
            <div class="card-footer row bg-white justify-content-between align-items-center">
                <div>
                    <small class="d-block card-text text-warning"><?php echo $advert["_source"]["type"] == "veriliyor" ? "Satılık" : "Kiralık" ?></small>
                    <small class="d-block card-text text-success"><?php echo $advert["_source"]["status"] == 1 ? "Sıfır" : "İkinci El" ?></small>
                </div>
                <div>
                    <small class="d-block card-text text-black"><?php echo $advert["_source"]["categories"][0]["category_name"] ?></small>
                    <small class="d-block card-text text-primary"><?php echo $advert["_source"]["categories"][0]["parent_name"] ?></small>
                </div>
                <div>
                    <small class="d-block card-text text-secondary"><?php echo $advert["_source"]["country"] ?></small>
                    <small class="d-block card-text text-info"><?php echo $advert["_source"]["city"] ?></small>
                    <small class="d-block card-text text-secondary"><?php echo $advert["_source"]["district"] ?></small>
                </div>
                <small class="d-block card-text font-weight-bold text-danger"><?php echo $advert["_source"]["price"] . " " . $advert["_source"]["currency"] ?> </small>
            </div>
            <small class="d-block card-text text-white"><?php echo $advert["_source"]["warehouse"] ?></small>
        </div>
    <?php endforeach; ?>
</div>