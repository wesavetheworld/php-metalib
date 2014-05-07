<?php

namespace BCLib\MetaLib;


class ResourceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_client;

    public function setUp()
    {
        $this->_client = $this->getMockBuilder('\BCLib\MetaLib\Client')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRetrieveByCategorySendsCorrectParameters()
    {
        $response = $this->_loadXML('resources-01.xml');

        $params = [
            'category_id' => 'foo'
        ];

        $this->_client->expects($this->once())
            ->method('send')
            ->with('retrieve_resources_by_category_request', $params, true)
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $resources->retrieveByCategory('foo');
    }

    public function testRetrieveByCategoryRetrieves()
    {
        $expected = [
            $this->_createResource('000003209', 'BCL03643', 'Database Number One', 'Database One', false),
            $this->_createResource('000007958', 'BCL06327', 'Database Number Two', 'Database Two', true),
        ];

        $response = $this->_loadXML('resources-01.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $this->assertEquals($expected, $resources->retrieveByCategory('foo'));
    }

    public function testDefaultParamsAreSet()
    {
        $response = $this->_loadXML('resources-01.xml');

        $params = [
            'category_id'  => 'foo',
            'requester_ip' => '127.0.0.1',
            'institute'    => 'bar'
        ];

        $this->_client->expects($this->once())
            ->method('send')
            ->with('retrieve_resources_by_category_request', $params, true)
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client, '127.0.0.1', 'bar');
        $resources->retrieveByCategory('foo');
    }

    public function testRetrieveCategoriesSendsCorrectParameters()
    {
        $response = $this->_loadXML('categories-01.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->with('retrieve_categories_request', [], true)
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $resources->retrieveCategories();
    }

    public function testRetrtieveCategoriesRetrieves()
    {
        $expected = [];

        $category = new Category();
        $category->name = 'Reference';
        $category->subcategories = [
            $this->_createSubCategory('ALL', '000000000', '000001313'),
            $this->_createSubCategory('Biography', '000000000', '000001315')
        ];
        $expected[] = $category;

        $category = new Category();
        $category->name = 'Interdisciplinary';
        $category->subcategories = [
            $this->_createSubCategory('General', '000000003', '000000413'),
        ];
        $expected[] = $category;

        $response = $this->_loadXML('categories-01.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $this->assertEquals($expected, $resources->retrieveCategories());
    }

    public function testRetrieveQuickSetsSendsCorrectParameters()
    {
        $response = $this->_loadXML('quicksets-01.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->with('retrieve_quick_sets_request', [], true)
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $resources->retrieveQuickSets();
    }

    public function testRetrtieveQuicksetsRetrieves()
    {
        $expected = [
            $this->_createQuickSet('Art/Architecture', '000037683', 'This is a sample', '000000006'),
            $this->_createQuickSet('Boston Libraries', '000037682', '', '000000008')
        ];

        $response = $this->_loadXML('quicksets-01.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $this->assertEquals($expected, $resources->retrieveQuickSets());
    }

    public function testRetrieveByQuickSetSendsCorrectParameters()
    {
        $response = $this->_loadXML('resources-02.xml');

        $params = [
            'quick_sets_id' => '000037683'
        ];

        $this->_client->expects($this->once())
            ->method('send')
            ->with('retrieve_resources_by_quick_set_request', $params, true)
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $resources->retrieveByQuickSet('000037683');
    }

    public function testRetrieveByQuickSetIdRetrieves()
    {
        $expected = [
            $this->_createResource('000001807', 'BCL02374', 'Database Number One', 'Database One', false),
            $this->_createResource('000001845', 'BCL02363', 'Database Number Two', 'Database Two', true),
        ];

        $response = $this->_loadXML('resources-02.xml');

        $this->_client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $resources = new ResourceService($this->_client);
        $this->assertEquals($expected, $resources->retrieveByQuickSet('000037683'));
    }

    protected function _loadXML($file)
    {
        return simplexml_load_file(__DIR__ . '/../../fixtures/' . $file);
    }

    protected function _createSubCategory($name, $base, $sequence)
    {
        $sub = new Subcategory();
        $sub->name = $name;
        $sub->bases = $base;
        $sub->sequence = $sequence;
        return $sub;
    }

    protected function _createResource($int_num, $num, $name, $short, $searchable)
    {
        $source = new Resource();
        $source->internal_number = $int_num;
        $source->number = $num;
        $source->name = $name;
        $source->short_name = $short;
        $source->searchable = $searchable;
        return $source;
    }

    protected function _createQuickSet($name, $seq, $description, $bases)
    {
        $set = new QuickSet();
        $set->name = $name;
        $set->sequence = $seq;
        $set->description = $description;
        $set->bases = $bases;
        return $set;
    }
}
 