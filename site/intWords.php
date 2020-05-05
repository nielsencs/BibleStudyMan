                <p>For certain interesting words:<br />
                <input type="checkbox" name="highlightSW" id="highlightSW"
                  <?php if($bHighlightSW){echo 'checked';}; ?>
                       onclick="doSubmit()"><label>highlight them</label> 
                <input type="checkbox" name="showOW"      id="showOW"
                  <?php if($bShowOW){echo 'checked';}; ?>
                       onclick="doSubmit()"><label>Show Hebrew/Greek</label> 
                </p>
