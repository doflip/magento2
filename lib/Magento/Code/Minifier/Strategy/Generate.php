<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Minification strategy that generates minified file, if it does not exist or outdated
 */
class Magento_Code_Minifier_Strategy_Generate implements Magento_Code_Minifier_StrategyInterface
{
    /**
     * @var Magento_Code_Minifier_AdapterInterface
     */
    protected $_adapter;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @param Magento_Code_Minifier_AdapterInterface $adapter
     * @param Magento_Filesystem $filesystem
     */
    public function __construct(
        Magento_Code_Minifier_AdapterInterface $adapter,
        Magento_Filesystem $filesystem
    ) {
        $this->_adapter = $adapter;
        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
    }

    /**
     * Get path to minified file for specified original file
     *
     * @param string $originalFile path to original file
     * @param string $targetFile
     */
    public function minifyFile($originalFile, $targetFile)
    {
        if ($this->_isUpdateNeeded($originalFile, $targetFile)) {
            $content = $this->_filesystem->read($originalFile);
            $content = $this->_adapter->minify($content);
            $this->_filesystem->write($targetFile, $content);
            $this->_filesystem->touch($targetFile, $this->_filesystem->getMTime($originalFile));
        }
    }

    /**
     * Check whether minified file should be created/updated
     *
     * @param string $originalFile
     * @param string $minifiedFile
     * @return bool
     */
    protected function _isUpdateNeeded($originalFile, $minifiedFile)
    {
        return !$this->_filesystem->has($minifiedFile)
            || ($this->_filesystem->getMTime($originalFile) != $this->_filesystem->getMTime($minifiedFile));
    }
}
