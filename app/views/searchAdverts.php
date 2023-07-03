<div class="row justify-content-center">
    <div class="col-md-3">
        <div class="card">
            <div class="card mx-2 mt-2">
                <div class="card-body text-center">
                    <small class="font-weight-bold text-center"> <span class="text-danger"><?php echo ucfirst($keyword) ?></span> kelimesi için arama sonucunda <span class="text-danger"><?php echo $advertCount ?></span> sonuç bulundu.</small>
                </div>
            </div>
            <div class="card-body">
                <h5 class="card-title">Filtreleme Seçenekleri</h5>
                <form action="searchByWarehouse" method="POST" id="searchForm">
                    <input type="hidden" value="<?php echo $keyword ?>" name="keyword">
                    <input type="hidden" value="<?php echo $page ?>" id="page" name="page">
                    <div class="form-group">
                        <label for="parentCategory">Ana Kategori</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($parentCategory) ?>" name="parentCategory" id="parentCategory">
                    </div>
                    <div class="form-group">
                        <label for="subCategory">Alt Kategori</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($subCategory) ?>" name="subCategory" id="subCategory">
                    </div>
                    <div class="form-group">
                        <label for="type">İlan Tipi</label>
                        <select class="form-control" name="type" id="type">
                            <option value="" selected>Seçiniz</option>
                            <option value="veriliyor" <?php echo $type === "veriliyor" ? "selected" : "" ?>>Satılık</option>
                            <option value="kiralik" <?php echo $type === "kiralik" ? "selected" : "" ?>>Kiralık</option>
                            <option value="araniyor" <?php echo $type === "araniyor" ? "selected" : "" ?>>Aranıyor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">İlan Durumu</label>
                        <select class="form-control" name="status" id="status">
                            <option value="" selected>Seçiniz</option>
                            <option value="1" <?php echo $status === "1" ? "selected" : "" ?>>Sıfır</option>
                            <option value="2" <?php echo $status === "2" ? "selected" : "" ?>>İkinci El</option>
                        </select>
                    </div>
                    <div class="row justify-content-between align-items-center">
                        <div class="col-6 form-group">
                            <label for="city">Şehir</label>
                            <input type="text" class="form-control" value="<?php echo $city ?>" name="city" id="city">
                        </div>
                        <div class="col-6 form-group">
                            <label for="district">İlçe</label>
                            <input type="text" class="form-control" value="<?php echo $district ?>" name="district" id="district">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="sort">Sıralama</label>
                        <select class="form-control" name="sort" id="sort">
                            <option value="" selected>Seçiniz</option>
                            <option value="ascByPrice" <?php echo $sortingOption === "ascByPrice" ? "selected" : "" ?>>Fiyata Göre Artan</option>
                            <option value="descByPrice" <?php echo $sortingOption === "descByPrice" ? "selected" : "" ?>>Fiyata Göre Azalan</option>
                            <option value="sortByCreatedAt" <?php echo $sortingOption === "sortByCreatedAt" ? "selected" : "" ?>>Yeni İlanlar</option>
                            <option value="sortByUpdatedAt" <?php echo $sortingOption === "sortByUpdatedAt" ? "selected" : "" ?>>Güncel İlanlar</option>
                            <option value="filterByIndividual" <?php echo $sortingOption === "filterByIndividual" ? "selected" : "" ?>>Bireysel İlanlar</option>
                            <option value="filterByStore" <?php echo $sortingOption === "filterByStore" ? "selected" : "" ?>>Sanal Mağaza İlanları</option>
                        </select>
                    </div>
                    <button type="submit" onclick="applyFilter()" class="btn btn-primary">Filtreyi Uygula</button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Kategori Seçiniz</h5>
                <ul class="list-group">
                    <?php foreach ($categories as $category) : ?>
                        <li class="list-group-item d-block justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none <?php echo $category['key'] === $category_parent_name  ?  "text-danger" : "text-secondary" ?>" onclick="parentCategorySelected('<?php echo $category['key'] ?>')">
                                <small><?php echo $category['key']; ?></small></a>
                            <span class="badge badge-primary badge-pill ml-1"><?php echo $category['doc_count']; ?></span>
                            <hr>
                            <ul>
                                <?php foreach ($this->elasticsearch->getCategoryBuckets($category['key'], "parent_name.keyword") as $subCategory) : ?>
                                    <li class="justify-content-between align-items-center">
                                        <a href="#" class="text-decoration-none <?php echo $subCategory['key'] === $category_name ?  "text-danger" : "text-secondary" ?>" onclick="subCategorySelected('<?php echo $subCategory['key'] ?>','<?php echo $category['key'] ?>')">
                                            <small><?php echo $subCategory['key']; ?></small></a>
                                        <span class="badge badge-primary badge-pill ml-1"><?php echo $subCategory['doc_count']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Şehir Seçiniz</h5>
                <ul class="list-group">
                    <?php foreach ($cities as $city) : ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="#" class="text-decoration-none <?php echo $city['key'] === $city ?  "text-danger" : "text-secondary" ?>" onclick="citySelected('<?php echo $city['key'] ?>')">
                                <small><?php echo $city['key']; ?></small></a>
                            <span class="badge badge-primary badge-pill ml-1"><?php echo $city['doc_count']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Tür</h5>
                <ul class="list-group">

                    <?php foreach ($statuses as $status) : ?>
                        <?php if ($status['key'] !== "") : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="#" class="text-decoration-none <?php echo $status['key'] === $el ?  "text-danger" : "text-secondary" ?>" onclick="statusSelected('<?php echo $status['key'] ?>')">
                                    <small><?php echo $status['key'] === "1" ? "Sıfır" : "İkinci El"; ?></small></a>
                                <span class="badge badge-primary badge-pill ml-1"><?php echo $status['doc_count']; ?></span>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-8 p-0">
        <div class="card border-0 p-0">
            <div class="card-body p-0 m-0">
                <div class="row justify-content-center">
                    <?php if (intval($page) === 1) : ?>
                        <?php foreach ($dopingAdverts as $advert) : ?>
                            <div class="card bg-success col-11 row mx-0 mb-2">
                                <div class="card-body row text-center justify-content-between align-items-center">
                                    <div>
                                        <?php echo '<a href="show' . '?params=' . $advert["_source"]['id'] . '">'; ?>
                                        <div class="row text-light justify-content-between align-items-center">
                                            <small class="d-block font-weight-bold"> <?php echo $advert["_source"]["name"] . " | " . $advert["_source"]["updated_at"] . " | " . $advert["_score"] ?> </small>
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

                        <?php foreach ($storeAdverts as $advert) : ?>
                            <div class="card bg-warning col-11 row mx-0 mb-2">
                                <div class="card-body row text-center justify-content-between align-items-center">
                                    <div>
                                        <?php echo '<a href="show' . '?params=' . $advert["top_hits"]["hits"]["hits"][0]["_source"]['id'] . '">'; ?>
                                        <div class="row text-light justify-content-between align-items-center">
                                            <small class="d-block font-weight-bold"> <?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["name"] . " | " . $advert["top_hits"]["hits"]["hits"][0]["_source"]["updated_at"] ?> </small>
                                            <div class="mx-2">(
                                                <?php
                                                if (isset($advert["top_hits"]["hits"]["hits"][0]["_source"]["is_doping"])) {


                                                    if ($advert["top_hits"]["hits"]["hits"][0]["_source"]["is_doping"] == 1) {
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
                                        <small class="d-block text-light card-text"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["company_name"] ?></small>
                                    </div>
                                </div>
                                </a>
                                <div class="card-footer row bg-white justify-content-between align-items-center">
                                    <div>
                                        <small class="d-block card-text text-warning"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["type"] == "veriliyor" ? "Satılık" : "Kiralık" ?></small>
                                        <small class="d-block card-text text-success"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["status"] == 1 ? "Sıfır" : "İkinci El" ?></small>
                                    </div>
                                    <div>
                                        <small class="d-block card-text text-black"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["categories"][0]["category_name"] ?></small>
                                        <small class="d-block card-text text-primary"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["categories"][0]["parent_name"] ?></small>
                                    </div>
                                    <div>
                                        <small class="d-block card-text text-secondary"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["country"] ?></small>
                                        <small class="d-block card-text text-info"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["city"] ?></small>
                                        <small class="d-block card-text text-secondary"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["district"] ?></small>
                                    </div>
                                    <div class="text-right">
                                        <small class="d-block card-text font-weight-bold text-danger"><?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["price"] . " " . $advert["top_hits"]["hits"]["hits"][0]["_source"]["currency"] ?></small>
                                        <?php
                                        if (intval($advert["doc_count"]) > 1)
                                            echo '<a href="searchAdditionAdverts' . '?params=' . $advert["top_hits"]["hits"]["hits"][0]["_source"]['company_id'] . '&query=' . urlencode(serialize($query)) . '&advertId=' . $advert["top_hits"]["hits"]["hits"][0]["_id"] .  '"><small class="font-weight-bold d-block m-1 card-text text-warning">+' . (intval($advert["doc_count"]) - 1)  . ' ilan</small></a>';
                                        ?>
                                    </div>
                                </div>
                                <small class="d-block card-text text-white"> <?php echo $advert["top_hits"]["hits"]["hits"][0]["_source"]["keywords"] ?> </small>
                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>
                    <?php foreach ($adverts as $advert) : ?>
                        <div class="card bg-primary col-11 row mx-0 mb-2">
                            <div class="card-body row text-center justify-content-between align-items-center">
                                <div>
                                    <?php echo '<a href="show' . '?params=' . $advert["_id"] . '">'; ?>
                                    <div class="row text-light justify-content-between align-items-center">
                                        <small class="d-block font-weight-bold"> <?php echo $advert["_source"]["name"] . " | " . $advert["_source"]["updated_at"] . " | " . $advert["_score"] ?> </small>
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
            </div>
            <div class="text-center">
                <h5> <?php echo $result ?></h5>
            </div>
            <div class="container">
                <div class="d-flex justify-content-center">
                    <nav>
                        <ul class="pagination">
                            <?php
                            if ($page > 1) {
                                echo '<li class="page-item"><a class="page-link" onclick="onPageClick(' . ($page - 1) . ')">Önceki</a></li>';
                            }
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $active = ($i == $page) ? ' active' : '';
                                echo '<li class="page-item' . $active . '"><a class="page-link" onclick="onPageClick(' . $i . ')">' . $i . '</a></li>';
                            }
                            if ($page < $totalPages) {
                                echo '<li class="page-item"><a class="page-link" onclick="onPageClick(' . ($page + 1) . ')">Sonraki</a></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function onPageClick(page) {
        document.getElementById("page").value = page
        document.getElementById("searchForm").submit();
    }

    function applyFilter() {
        document.getElementById("page").value = 1
        document.getElementById("searchForm").submit();
    }

    function parentCategorySelected(category) {
        document.getElementById("page").value = 1
        document.getElementById("parentCategory").value = category
        document.getElementById("searchForm").submit();
    }

    function subCategorySelected(category, parentCategory) {
        document.getElementById("page").value = 1
        document.getElementById("subCategory").value = category
        document.getElementById("parentCategory").value = parentCategory
        document.getElementById("searchForm").submit();
    }

    function citySelected(city) {
        document.getElementById("page").value = 1
        document.getElementById("city").value = city
        document.getElementById("searchForm").submit();
    }

    function statusSelected(status) {
        document.getElementById("page").value = 1
        document.getElementById("status").value = status
        document.getElementById("searchForm").submit();
    }
</script>