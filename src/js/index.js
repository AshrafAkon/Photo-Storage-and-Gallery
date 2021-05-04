import Sidebar from './components/Sidebar';
import Search from './components/Search';
import Upload from './components/Upload';
import DropTarget from './components/DropTarget';
import singleImageDropManager from './components/SingleImageDropManager';

import './components/Tags';

// Initialize things, use `.destroy()` to clean up when needed
const contentSidebar = new Sidebar(document.querySelector('.js-content-sidebar'));
const imageDetailsSidebar = new Sidebar(document.querySelector('.js-image-details-sidebar'));
const search = new Search(document.querySelector('.js-search'));
const upload = new Upload(document.querySelector('.js-upload'));
const globalDropTarget = new DropTarget(document.querySelector('.js-drop-target'), [
  'png',
  'gif',
  'tiff',
  'tif',
  'jpeg',
  'jpg',
  'psd',
]);
