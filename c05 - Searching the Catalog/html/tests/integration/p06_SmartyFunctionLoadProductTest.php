require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../presentation/smarty_plugins/06.function.load_product.php';
use PHPUnit\Framework\TestCase;
use Smarty\Smarty;

class SmartyFunctionLoadProductTest extends TestCase
{
    public function testLoadProductWithValidProductID()
    {
        $_GET['ProductID'] = 1;
        $params = ['assign' => 'product'];
        $smarty = new Smarty();
        smarty_function_load_product($params, $smarty);
        $product = $smarty->getTemplateVars('product');

        $this->assertInstanceOf(Product::class, $product);
        $this->assertNotNull($product->mProduct);
    }

    public function testLoadProductWithoutProductID()
    {
        unset($_GET['ProductID']);
        $params = ['assign' => 'product'];
        $smarty = new Smarty();

        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        smarty_function_load_product($params, $smarty);
    }

    public function testLoadProductWithPageLink()
    {
        $_GET['ProductID'] = 1;
        $_SESSION['page_link'] = 'custom_page.php';
        $params = ['assign' => 'product'];
        $smarty = new Smarty();
        smarty_function_load_product($params, $smarty);
        $product = $smarty->getTemplateVars('product');

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('custom_page.php', $product->mPageLink);
    }
}