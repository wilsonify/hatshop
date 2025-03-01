<?php
// Plugin functions inside plugin files must be named: smarty_type_name
function smarty_function_load_cart_details($params, $smarty)
{

  $cart_details = new CartDetails();
  $cart_details->init();

  // Assign template variable
  $smarty->assign($params['assign'], $cart_details);
}

// Class that deals with managing the shopping cart
class CartDetails
{
  // Public variables available in smarty template
  public $mCartProducts;
  public $mSavedCartProducts;
  public $mTotalAmount;
  public $mIsCartNowEmpty = 0; // Is the shopping cart empty?
  public $mIsCartLaterEmpty = 0; // Is the 'saved for later' list empty?
  public $mCartReferrer = 'index.php';
  public $mCartDetailsTarget;
  public $mRecommendations;
  public $mCheckoutActive = false;
  public $mCheckoutLink;

  // Private attributes
  private $_mProductId;
  private $_mCartAction;

  // Class constructor
  public function __construct()
  {
    $url_base = substr(getenv('REQUEST_URI'),
                       strrpos(getenv('REQUEST_URI'), '/') + 1,
                       strlen(getenv('REQUEST_URI')) - 1);

    $url_parameter_prefix = (empty ($_GET) ? '?' : '&');

    $this->mCheckoutLink = $url_base . $url_parameter_prefix . 'Checkout';

    // Setting the "Continue shopping" button target
    if (isset ($_SESSION['page_link']))
      $this->mCartReferrer = $_SESSION['page_link'];

    if (isset ($_GET['CartAction']))
      $this->_mCartAction = $_GET['CartAction'];
    else
      trigger_error('CartAction not set', E_USER_ERROR);

    // These cart operations require a valid product id
    if ($this->_mCartAction == ADD_PRODUCT ||
        $this->_mCartAction == REMOVE_PRODUCT ||
        $this->_mCartAction == SAVE_PRODUCT_FOR_LATER ||
        $this->_mCartAction == MOVE_PRODUCT_TO_CART)

    if (isset ($_GET['ProductID']))
      $this->_mProductId = $_GET['ProductID'];
    else
      trigger_error('ProductID must be set for this type of request',
                    E_USER_ERROR);

    $this->mCartDetailsTarget = 'index.php?CartAction=' .
                                UPDATE_PRODUCTS_QUANTITIES;
  }

  public function init()
  {
    switch ($this->_mCartAction)
    {
      case ADD_PRODUCT:
        ShoppingCart::AddProduct($this->_mProductId);
        header('Location: ' . $this->mCartReferrer);

        break;
      case REMOVE_PRODUCT:
        ShoppingCart::RemoveProduct($this->_mProductId);

        break;
      case UPDATE_PRODUCTS_QUANTITIES:
        ShoppingCart::Update($_POST['productID'], $_POST['quantity']);

        break;
      case SAVE_PRODUCT_FOR_LATER:
        ShoppingCart::SaveProductForLater($this->_mProductId);

        break;
      case MOVE_PRODUCT_TO_CART:
        ShoppingCart::MoveProductToCart($this->_mProductId);

        break;
      default:
        // Do nothing
        break;
    }

    // Calculate the total amount for the shopping cart
    $this->mTotalAmount = ShoppingCart::GetTotalAmount();

    if ($this->mTotalAmount != 0 && Customer::IsAuthenticated())
      $this->mCheckoutActive = true;

    // Get shopping cart products
    $this->mCartProducts =
      ShoppingCart::GetCartProducts(GET_CART_PRODUCTS);

    // Gets the Saved for Later products
    $this->mSavedCartProducts =
      ShoppingCart::GetCartProducts(GET_CART_SAVED_PRODUCTS);

    // Check whether we have an empty shopping cart
    if (count($this->mCartProducts) == 0)
      $this->mIsCartNowEmpty = 1;

    // Check whether we have an empty Saved for Later list
    if (count($this->mSavedCartProducts) == 0)
      $this->mIsCartLaterEmpty = 1;

    // Build the links for cart actions
    for ($i = 0; $i < count($this->mCartProducts); $i++)
    {
      $this->mCartProducts[$i]['save'] = 'index.php?ProductID=' .
        $this->mCartProducts[$i]['product_id'] .
        '&CartAction=' . SAVE_PRODUCT_FOR_LATER;

      $this->mCartProducts[$i]['remove'] = 'index.php?ProductID=' .
        $this->mCartProducts[$i]['product_id'] .
        '&CartAction=' . REMOVE_PRODUCT;
    }

    for ($i = 0; $i < count($this->mSavedCartProducts); $i++)
    {
      $this->mSavedCartProducts[$i]['move'] = 'index.php?ProductID=' .
        $this->mSavedCartProducts[$i]['product_id'] .
        '&CartAction=' . MOVE_PRODUCT_TO_CART;

      $this->mSavedCartProducts[$i]['remove'] = 'index.php?ProductID=' .
        $this->mSavedCartProducts[$i]['product_id'] .
        '&CartAction=' . REMOVE_PRODUCT;
    }

    // Get product recommendations for the shopping cart
    $this->mRecommendations =
      ShoppingCart::GetRecommendations();

    // Create recommended product links
    for ($i = 0; $i < count($this->mRecommendations); $i++)
      $this->mRecommendations[$i]['link'] = 'index.php?ProductID=' .
        $this->mRecommendations[$i]['product_id'];
  }
}

