
<h3>Oops! Something went wrong</h3>

<div><?php echo $e->getMessage(); ?> in <?php echo $e->getFile(); ?> on line <?php echo $e->getLine(); ?></div>
<div><?php $trace = $e->getTrace(); var_dump($trace); ?></div>

