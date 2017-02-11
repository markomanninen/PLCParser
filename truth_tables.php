			<a id="Truth-tables"></a>
			<div class="page-header">
				<h3>Truth tables</h3>
			</div>

			<div class="col-md-12">
				<h3>Unary</h3>
				<p>An operation with only one operand, i.e. a single input.</p>
			</div>

			<div class="table-responsive col-md-5">

				<table class="table table-bordered">
				<caption>NOT</caption>
				<thead>
				  <tr><th>A</th><th>(not A) = </th></tr>
				</thead>
				<tbody>
				  <tr><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>
				  
			</div>

			<div class="col-md-12">
					<h3>Binary</h3>
				  
					<p>Calculates an operation of a set that combines two elements / inputs.</p>
				  
					<p>Complementary operations (NAND, NOR, XNOR) can also be handled with NOT operator. For example (1 nand 1) is same as !(1 and 1).</p>
			</div>

			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>OR</caption>
				<thead>
				  <tr><th>A</th><th>B</th><th>(A or B)</th></tr>
				</thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>
				  
			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>NOR</caption>
				<thead><tr><th>A</th><th>B</th><th>(A nor B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>AND</caption>
				<thead><tr><th>A</th><th>B</th><th>(A and B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>NAND</caption>
				<thead><tr><th>A</th><th>B</th><th>(A nand B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

			</div>

			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XOR</caption>
				<thead><tr><th>A</th><th>B</th><th>(A xor B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XNOR</caption>
				<thead><tr><th>A</th><th>B</th><th>(A xnor B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

				</div>
				  
				<div class="col-md-12">
				  <h3>Multiary</h3>
				  
				  <p>All other operators except NOT supports ternary and other n-ary operations. This means that operation can be done to a group of items, not only for two operands. But note that internally logical structure is parsed to binary tree anyway, so ternary operation is just a matter of syntax preference over repeating same operator multipe times. For example (1 or 0 or 0) can be written: (or 1 0 0) and will internally be interpreted as: (1 or (0 or 0)).
					</p><p>
					Special attention with XOR is required here. XOR is taken as parity check. In other words if odd number of true values is in the set, then result is TRUE. 
					</p><p>
					In logic circuits ternary input is sometimes used and it is even more common operation in programming languages.</p>
				  
			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XOR</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>(xor A B C) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XNOR</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>(xnor A B C) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

			</div>
