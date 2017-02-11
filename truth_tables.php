			<a id="Truth-tables"></a>
			<div class="page-header">
				<h3>Truth tables</h3>
			</div>

			<div class="col-md-12">
				<h3>Unary</h3>
				<p>An operation with only one operand, i.e. a single input. IS is tautology, not really needed for the practical purposes.</p>
			</div>

			<div class="table-responsive col-md-5">

				<table class="table table-bordered">
				<caption>IS</caption>
				<thead>
				  <tr><th>A</th><th>(is A)</th></tr>
				</thead>
				<tbody>
				  <tr><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>
				  
			</div>

			<div class="table-responsive col-md-5">

				<table class="table table-bordered">
				<caption>NOT</caption>
				<thead>
				  <tr><th>A</th><th>is not (A) = (is not A) = not (A) = (not A) = </th></tr>
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
				  
					<p>Complementary operations (NAND, NOR, XNOR) are handled with NOT operator.</p>
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
				<thead><tr><th>A</th><th>B</th><th>not (A or B) = </th></tr></thead>
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
				<thead><tr><th>A</th><th>B</th><th>not (A and B) = </th></tr></thead>
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
				<thead><tr><th>A</th><th>B</th><th>xor (A B) = </th></tr></thead>
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
				<thead><tr><th>A</th><th>B</th><th>not xor (A B) = </th></tr></thead>
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
				  
				  <p>Ternary and other n-ary operations are handled with XOR and XAND operators. 
					Special attention is required here. XOR is taken as "if one and only one" of the set is true. 
					Thus operation can be done to a group of items, not only for two operands. 
					In logic circuits ternary input is sometimes used and it is even more common operation in programming languages.</p>
				  
				  <p>XAND is debated and sometimes thought to be a synonym for XNOR, but here it is an extension of XOR and means 
					"if one or more, but not all" of the set is true.</p>
				  
				  <p>Complements XNOR and XNAND are again handled with not operator.</p>
				  
			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XOR</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>xor (A B C) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XNOR</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>not xor (A B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

			</div>
				
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XAND</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>xand (A B C) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				</tbody>
				</table>

			</div>
			  
			<div class="table-responsive col-md-4">

				<table class="table table-bordered">
				<caption>XNAND</caption>
				<thead><tr><th>A</th><th>B</th><th>C</th><th>not xand (A B) = </th></tr></thead>
				<tbody>
				  <tr><td>1</td><td>0</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>0</td><td class="danger">false</td></tr>
				  <tr><td>0</td><td>1</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>0</td><td>1</td><td class="danger">false</td></tr>
				  <tr><td>1</td><td>1</td><td>1</td><td class="success">true</td></tr>
				  <tr><td>0</td><td>0</td><td>0</td><td class="success">true</td></tr>
				</tbody>
				</table>

			</div>
