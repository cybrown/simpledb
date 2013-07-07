<?php
namespace Cy\SimpleDB;

interface PersistenceHandler {
	function __construct($path);
	function load();
	function save();
	function loadForCollection($name);
	function saveFromCollection($name, $data);
	function hasKey($name);
}
