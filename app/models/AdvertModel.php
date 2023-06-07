<?php

class AdvertModel
{
    public function getAdverts($scrollId)
    {
        return getAdvertsByLimit(25, $scrollId);
    }

    public function getAdvertDetail($id)
    {
        return getAdvertById($id);
    }

    public function setAdvert($request, $type)
    {
        if ($type === "store") {
            return indexAdvert($request);
        }
        if ($type === "update") {
            return updateAdvert($request);
        }
    }

    public function searchAdverts($keyword, $filterOptions, $sortingOption, $from, $pageSize, $page)
    {
        return getAdvertsBySearch($keyword, $filterOptions, $sortingOption, $from, $pageSize, $page);
    }
}
