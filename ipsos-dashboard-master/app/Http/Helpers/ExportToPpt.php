<?php

namespace App\Http\Helpers;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Area;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar3D;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Line;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie3D;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Scatter;
use PhpOffice\PhpPresentation\Shape\Chart\Series;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Border;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;
use PhpOffice\PhpPresentation\Style\Shadow;
use PhpOffice\Common\Adapter\Zip\PclZipAdapter;
use PhpOffice\Common\Adapter\Zip\ZipArchiveAdapter;

class ExportToPpt
{
    public function export($data, $file)
    {
        $objPHPPresentation = $this->init();
        $this->chart($objPHPPresentation);

        $oWriterPPTX = IOFactory::createWriter($objPHPPresentation, 'PowerPoint2007');
        // $oWriterPPTX->setZipAdapter(PclZipAdapter);
        $oWriterPPTX->save(storage_path('app'.DIRECTORY_SEPARATOR.$file));
    }

    private function init()
    {
        // Create new PHPPresentation object
        // echo date('H:i:s') . ' Create new PHPPresentation object';
        $objPHPPresentation = new PhpPresentation();
        
        // Set properties
        // echo date('H:i:s') . ' Set properties';
        $objPHPPresentation->getDocumentProperties()->setCreator('PHPOffice')
                                            ->setLastModifiedBy('PHPPresentation Team')
                                            ->setTitle('Sample 08 Title')
                                            ->setSubject('Sample 08 Subject')
                                            ->setDescription('Sample 08 Description')
                                            ->setKeywords('office 2007 openxml libreoffice odt php')
                                            ->setCategory('Sample Category');
        
        // Remove first slide
        // echo date('H:i:s') . ' Remove first slide';
        $objPHPPresentation->removeSlideByIndex(0);
        
        return $objPHPPresentation;
    }

    /**
     * Creates a templated slide
     *
     * @param PHPPresentation $objPHPPresentation
     * @return \PhpOffice\PhpPresentation\Slide
     */
    private function createTemplatedSlide(PhpPresentation $objPHPPresentation)
    {
        // Create slide
        $slide = $objPHPPresentation->createSlide();
        
        // Return slide
        return $slide;
    }

    private function simple(PhpPresentation $objPHPPresentation)
    {
        // Create slide
        // echo date('H:i:s') . ' Create slide' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a shape (drawing)
        // echo date('H:i:s') . ' Create a shape (drawing)' . EOL;
        $shape = $currentSlide->createDrawingShape();
        $shape->setName('PHPPresentation logo')
            ->setDescription('PHPPresentation logo')
            ->setPath(public_path('img/avatar5.png'))
            ->setHeight(36)
            ->setOffsetX(10)
            ->setOffsetY(10);
        $shape->getShadow()->setVisible(true)
            ->setDirection(45)
            ->setDistance(10);
        $shape->getHyperlink()->setUrl('https://github.com/PHPOffice/PHPPresentation/')->setTooltip('PHPPresentation');
        // Create a shape (text)
        // echo date('H:i:s') . ' Create a shape (rich text)' . EOL;
        $shape = $currentSlide->createRichTextShape()
            ->setHeight(300)
            ->setWidth(600)
            ->setOffsetX(170)
            ->setOffsetY(180);
        $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $textRun = $shape->createTextRun('Thank you for using PHPPresentation!');
        $textRun->getFont()->setBold(true)
            ->setSize(60)
            ->setColor(new Color('FFE06B20'));
    }

    private function line(PhpPresentation $objPHPPresentation)
    {
        // Set Style
        $oFill = new Fill();
        $oFill->setFillType(Fill::FILL_SOLID)->setStartColor(new Color('FFE06B20'));
        $oShadow = new Shadow();
        $oShadow->setVisible(true)->setDirection(45)->setDistance(10);
        // Generate sample data for chart
        // echo date('H:i:s') . ' Generate sample data for chart' . EOL;
        $seriesData = array(
            'Monday' => 12,
            'Tuesday' => 15,
            'Wednesday' => 13,
            'Thursday' => 17,
            'Friday' => 14,
            'Saturday' => 9,
            'Sunday' => 7
        );
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a line chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a line chart (that should be inserted in a chart shape)' . EOL;
        $lineChart = new Line();
        $series = new Series('Downloads', $seriesData);
        $series->setShowSeriesName(true);
        $series->setShowValue(true);
        $lineChart->addSeries($series);
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart)' . EOL;
        $shape = $currentSlide->createChartShape();
        $shape->setName('PHPPresentation Daily Downloads')->setResizeProportional(false)->setHeight(550)->setWidth(700)->setOffsetX(120)->setOffsetY(80);
        $shape->setShadow($oShadow);
        $shape->setFill($oFill);
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getTitle()->setText('PHPPresentation Daily Downloads');
        $shape->getTitle()->getFont()->setItalic(true);
        $shape->getPlotArea()->setType($lineChart);
        $shape->getView3D()->setRotationX(30);
        $shape->getView3D()->setPerspective(30);
        $shape->getLegend()->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getLegend()->getFont()->setItalic(true);
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a line chart (that should be inserted in a shape)
        $oOutline = new \PhpOffice\PhpPresentation\Style\Outline();
        $oOutline->getFill()->setFillType(Fill::FILL_SOLID);
        $oOutline->getFill()->setStartColor(new Color(Color::COLOR_YELLOW));
        $oOutline->setWidth(2);
        // echo date('H:i:s') . ' Create a line chart (that should be inserted in a chart shape)' . EOL;
        $lineChart1 = clone $lineChart;
        $series1 = $lineChart1->getSeries();
        $series1[0]->setOutline($oOutline);
        $series1[0]->getMarker()->setSymbol(\PhpOffice\PhpPresentation\Shape\Chart\Marker::SYMBOL_DIAMOND);
        $series1[0]->getMarker()->setSize(7);
        $lineChart1->setSeries($series1);
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart1)' . EOL;
        // echo date('H:i:s') . ' Differences with previous : Values on right axis and Legend hidden' . EOL;
        $shape1 = clone $shape;
        $shape1->getLegend()->setVisible(false);
        $shape1->setName('PHPPresentation Weekly Downloads');
        $shape1->getTitle()->setText('PHPPresentation Weekly Downloads');
        $shape1->getPlotArea()->setType($lineChart1);
        $shape1->getPlotArea()->getAxisY()->setFormatCode('#,##0');
        $currentSlide->addShape($shape1);
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a line chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a line chart (that should be inserted in a chart shape)' . EOL;
        $lineChart2 = clone $lineChart;
        $series2 = $lineChart2->getSeries();
        $series2[0]->getFont()->setSize(25);
        $series2[0]->getMarker()->setSymbol(\PhpOffice\PhpPresentation\Shape\Chart\Marker::SYMBOL_TRIANGLE);
        $series2[0]->getMarker()->setSize(10);
        $lineChart2->setSeries($series2);
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart2)' . EOL;
        // echo date('H:i:s') . ' Differences with previous : Values on right axis and Legend hidden' . EOL;
        $shape2 = clone $shape;
        $shape2->getLegend()->setVisible(false);
        $shape2->setName('PHPPresentation Weekly Downloads');
        $shape2->getTitle()->setText('PHPPresentation Weekly Downloads');
        $shape2->getPlotArea()->setType($lineChart2);
        $shape2->getPlotArea()->getAxisY()->setFormatCode('#,##0');
        $currentSlide->addShape($shape2);
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide #3' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a line chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a line chart (that should be inserted in a chart shape)' . EOL;
        $lineChart3 = clone $lineChart;
        $oGridLines1 = new \PhpOffice\PhpPresentation\Shape\Chart\Gridlines();
        $oGridLines1->getOutline()->setWidth(10);
        $oGridLines1->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color(Color::COLOR_BLUE));
        $oGridLines2 = new \PhpOffice\PhpPresentation\Shape\Chart\Gridlines();
        $oGridLines2->getOutline()->setWidth(1);
        $oGridLines2->getOutline()->getFill()->setFillType(Fill::FILL_SOLID)->setStartColor(new Color(Color::COLOR_DARKGREEN));
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart3)' . EOL;
        // echo date('H:i:s') . ' Feature : Gridlines' . EOL;
        $shape3 = clone $shape;
        $shape3->setName('Shape 3');
        $shape3->getTitle()->setText('Chart with Gridlines');
        $shape3->getPlotArea()->setType($lineChart3);
        $shape3->getPlotArea()->getAxisX()->setMajorGridlines($oGridLines1);
        $shape3->getPlotArea()->getAxisY()->setMinorGridlines($oGridLines2);
        $currentSlide->addShape($shape3);
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide #4' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a line chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a line chart (that should be inserted in a chart shape)' . EOL;
        $lineChart4 = clone $lineChart;
        $oOutlineAxisX = new \PhpOffice\PhpPresentation\Style\Outline();
        $oOutlineAxisX->setWidth(2);
        $oOutlineAxisX->getFill()->setFillType(Fill::FILL_SOLID);
        $oOutlineAxisX->getFill()->getStartColor()->setRGB('012345');
        $oOutlineAxisY = new \PhpOffice\PhpPresentation\Style\Outline();
        $oOutlineAxisY->setWidth(5);
        $oOutlineAxisY->getFill()->setFillType(Fill::FILL_SOLID);
        $oOutlineAxisY->getFill()->getStartColor()->setRGB('ABCDEF');
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart4)' . EOL;
        // echo date('H:i:s') . ' Feature : Axis Outline' . EOL;
        $shape4 = clone $shape;
        $shape4->setName('Shape 4');
        $shape4->getTitle()->setText('Chart with Outline on Axis');
        $shape4->getPlotArea()->setType($lineChart4);
        $shape4->getPlotArea()->getAxisX()->setOutline($oOutlineAxisX);
        $shape4->getPlotArea()->getAxisY()->setOutline($oOutlineAxisY);
        $currentSlide->addShape($shape4);
        // Create templated slide
        // echo EOL . date('H:i:s') . ' Create templated slide #5' . EOL;
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation);
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart5)' . EOL;
        // echo date('H:i:s') . ' Feature : Gridlines' . EOL;
        $shape5 = clone $shape;
        $shape5->getPlotArea()->getAxisY()->setMinBounds(5);
        $shape5->getPlotArea()->getAxisY()->setMaxBounds(20);
        $currentSlide->addShape($shape5);
    }

    private function chart(PhpPresentation $objPHPPresentation)
    {
        // Create templated slide
        // echo date('H:i:s') . ' Create templated slide';
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation); // local function

        // Generate sample data for first chart
        // echo date('H:i:s') . ' Generate sample data for first chart';
        $series1Data = array('Jan' => 133, 'Feb' => 99, 'Mar' => 191, 'Apr' => 205, 'May' => 167, 'Jun' => 201, 'Jul' => 240, 'Aug' => 226, 'Sep' => 255, 'Oct' => 264, 'Nov' => 283, 'Dec' => 293);
        $series2Data = array('Jan' => 266, 'Feb' => 198, 'Mar' => 271, 'Apr' => 305, 'May' => 267, 'Jun' => 301, 'Jul' => 340, 'Aug' => 326, 'Sep' => 344, 'Oct' => 364, 'Nov' => 383, 'Dec' => 379);
        
        // Create a bar chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a bar chart (that should be inserted in a chart shape)';
        $bar3DChart = new Bar3D();
        $bar3DChart->addSeries( new Series('2009', $series1Data) );
        $bar3DChart->addSeries( new Series('2010', $series2Data) );
        
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart)';
        $shape = $currentSlide->createChartShape();
        $shape->setName('PHPPresentation Monthly Downloads')
                ->setResizeProportional(false)
                ->setHeight(550)
                ->setWidth(700)
                ->setOffsetX(120)
                ->setOffsetY(80)
                ->setIncludeSpreadsheet(true);
        $shape->getShadow()->setVisible(true)
                ->setDirection(45)
                ->setDistance(10);
        $shape->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
                            ->setStartColor(new Color('FFCCCCCC'))
                            ->setEndColor(new Color('FFFFFFFF'))
                            ->setRotation(270);
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getTitle()->setText('PHPPresentation Monthly Downloads');
        $shape->getTitle()->getFont()->setItalic(true);
        $shape->getPlotArea()->getAxisX()->setTitle('Month');
        $shape->getPlotArea()->getAxisY()->setTitle('Downloads');
        $shape->getPlotArea()->setType($bar3DChart);
        $shape->getView3D()->setRightAngleAxes(true);
        $shape->getView3D()->setRotationX(20);
        $shape->getView3D()->setRotationY(20);
        $shape->getLegend()->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getLegend()->getFont()->setItalic(true);
        
        // Create templated slide
        // echo date('H:i:s') . ' Create templated slide';
        $currentSlide = $this->createTemplatedSlide($objPHPPresentation); // local function
        
        // Generate sample data for second chart
        // echo date('H:i:s') . ' Generate sample data for second chart';
        $seriesData = array('Monday' => 12, 'Tuesday' => 15, 'Wednesday' => 13, 'Thursday' => 17, 'Friday' => 14, 'Saturday' => 9, 'Sunday' => 7);
        
        // Create a pie chart (that should be inserted in a shape)
        // echo date('H:i:s') . ' Create a pie chart (that should be inserted in a chart shape)';
        $pie3DChart = new Pie3D();
        $pie3DChart->addSeries( new Series('Downloads', $seriesData) );
        
        // Create a shape (chart)
        // echo date('H:i:s') . ' Create a shape (chart)';
        $shape = $currentSlide->createChartShape();
        $shape->setName('PHPPresentation Daily Downloads')
                ->setResizeProportional(false)
                ->setHeight(550)
                ->setWidth(700)
                ->setOffsetX(120)
                ->setOffsetY(80)
                ->setIncludeSpreadsheet(true);
        $shape->getShadow()->setVisible(true)
                            ->setDirection(45)
                            ->setDistance(10);
        $shape->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
                            ->setStartColor(new Color('FFCCCCCC'))
                            ->setEndColor(new Color('FFFFFFFF'))
                            ->setRotation(270);
        $shape->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getTitle()->setText('PHPPresentation Daily Downloads');
        $shape->getTitle()->getFont()->setItalic(true);
        $shape->getPlotArea()->setType($pie3DChart);
        $shape->getView3D()->setRotationX(30);
        $shape->getView3D()->setPerspective(30);
        $shape->getLegend()->getBorder()->setLineStyle(Border::LINE_SINGLE);
        $shape->getLegend()->getFont()->setItalic(true);
    }
}