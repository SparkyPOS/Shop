<?php

namespace Modules\AuctionProducts\Services;

use Modules\AuctionProducts\Repositories\AuctionProductsRepository;

class AuctionService{

    protected $auctionRepository;


    public function __construct(AuctionProductsRepository  $auctionRepository)
    {
        $this->auctionRepository = $auctionRepository;
    }

    public function getSellerProduct(){
        return $this->auctionRepository->getSellerProduct();
    }

    public function getAuctions($userType)
    {
        return $this->auctionRepository->getAuctions($userType);
    }

    public function storeAuction($request)
    {
        return $this->auctionRepository->storeAuction($request);
    }

    public function getAuctionById($id){
        return $this->auctionRepository->getAuctionById($id);
    }

    public function maxBidAmount($id)
    {
        return $this->auctionRepository->maxBidAmount($id);
    }


    public function update($request)
    {
        return $this->auctionRepository->update($request);
    }


    public function destroy($id)
    {
        return $this->auctionRepository->destroy($id);
    }

    public function destroyThisBid($id)
    {
        return $this->auctionRepository->destroyThisBid($id);
    }

    public function getActiveSellerProductById($seller_product_id)
    {
        return $this->auctionRepository->getActiveSellerProductById($seller_product_id);
    }

    public function savePlaceBid($request,$user_id)
    {
        return $this->auctionRepository->savePlaceBid($request,$user_id);
    }

    public function updateAuctionSettings($request)
    {
        return $this->auctionRepository->updateAuctionSettings($request);
    }

    public function cancelAuctionOrder($bid_id)
    {
        return $this->auctionRepository->cancelAuctionOrder($bid_id);
    }

    public function getViewAllBidsData($id)
    {
        return $this->auctionRepository->getViewAllBidsData($id);
    }

    public function getAllAuctionProduct($sort_by, $paginate)
    {
        return $this->auctionRepository->getAllAuctionProduct($sort_by, $paginate);
    }

    public function getSellerProductByAjax($search){
        return $this->auctionRepository->getSellerProductByAjax($search);
    }

}
