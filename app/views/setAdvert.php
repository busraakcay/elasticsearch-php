<form action="<?php echo $advertDetail["id"] !== null ? 'update' : 'store' ?>" method="POST">
    <input type="hidden" name="advertId" value="<?php echo $advertDetail["id"] ?>">
    <input type="hidden" name="docId" value="<?php echo $docId ?>">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="advertName">İlan Adı</label>
            <input type="text" name="advertName" value="<?php echo $advertDetail["name"] ?>" class="form-control" id="advertName">
        </div>
        <div class="form-group col-md-3">
            <label for="price">Fiyatı</label>
            <input type="text" name="price" value="<?php echo $advertDetail["price"] ?>" class="form-control" id="price">
        </div>
        <div class="form-group col-md-3">
            <label for="advertType">İlan Tipi</label>
            <select id="advertType" name="advertType" value="<?php echo $advertDetail["type"] ?>" class="form-control">
                <option <?php echo $advertDetail["type"] === "Satılık" ? "selected" : "" ?>>Satılık</option>
                <option <?php echo $advertDetail["type"] === "Kiralık" ? "selected" : "" ?>>Kiralık</option>
                <option <?php echo $advertDetail["type"] === "Aranıyor" ? "selected" : "" ?>>Aranıyor</option>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="advertStatus">İlan Durumu</label>
            <select id="advertStatus" name="advertStatus" class="form-control">
                <option <?php echo $advertDetail["status"] === "2. El" || $advertDetail["status"] === "İkinci El"  ? "selected" : "" ?>>İkinci El</option>
                <option <?php echo $advertDetail["status"] === "Sıfır" ? "selected" : "" ?>>Sıfır</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="categoryId">Kategori ID</label>
            <input type="text" value="<?php echo $advertDetail["category_id"] ?>" name="categoryId" class="form-control" id="categoryId">
        </div>
        <div class="form-group col-md-3">
            <label for="categoryName">Kategori Adı</label>
            <input type="text" value="<?php echo $advertDetail["category_name"] ?>" name="categoryName" class="form-control" id="categoryName">
        </div>
        <div class="form-group col-md-3">
            <label for="categoryParentId">Ana Kategori ID</label>
            <input type="text" name="categoryParentId" value="<?php echo $advertDetail["category_parent_id"] ?>" class="form-control" id="categoryParentId">
        </div>
        <div class="form-group col-md-3">
            <label for="categoryParentName">Ana Kategori Adı</label>
            <input type="text" name="categoryParentName" value="<?php echo $advertDetail["category_parent_name"] ?>" class="form-control" id="categoryParentName">
        </div>
    </div>
    <div class="form-group">
        <label for="description">Açıklama</label><br>
        <textarea rows="5" name="description" class="form-control" id="description"><?php echo $advertDetail["description"] ?></textarea>
    </div>
    <div class="form-group">
        <label for="keywords">Anahtar Kelimeler</label><br>
        <input type="text" name="keywords" value="<?php echo $advertDetail["keywords"] ?>" class="form-control" id="keywords">
    </div>
    <div class="form-row">
        <div class="form-group col-md-2">
            <label for="country">Ülke</label>
            <input type="text" name="country" value="<?php echo $advertDetail["country"] ?>" class="form-control" id="country">
        </div>
        <div class="form-group col-md-2">
            <label for="city">Şehir</label>
            <input type="text" name="city" value="<?php echo $advertDetail["city"] ?>" class="form-control" id="city">
        </div>
        <div class="form-group col-md-2">
            <label for="district">İlçe</label>
            <input type="text" name="district" value="<?php echo $advertDetail["district"] ?>" class="form-control" id="district">
        </div>
        <div class="form-group col-md-2">
            <label for="companyId">Firma ID</label>
            <input type="text" value="<?php echo $advertDetail["company_id"] ?>" name="companyId" class="form-control" id="companyId">
        </div>
        <div class="form-group col-md-2">
            <label for="companyName">Firma Adı</label>
            <input type="text" name="companyName" value="<?php echo $advertDetail["company_name"] ?>" class="form-control" id="companyName">
        </div>
        <div class="form-group col-md-2">
            <label for="companyType">Firma Türü</label>
            <select id="companyType" name="companyType" class="form-control">
                <option <?php echo $advertDetail["company_type"] === "Sanal Mağaza" ? "selected" : "" ?>>Sanal Mağaza</option>
                <option <?php echo $advertDetail["company_type"] === "Bireysel" ? "selected" : "" ?>>Bireysel</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Kaydet</button>
</form>