# Contao 4 Contao-Efg-Bundle 

Contao is an Open Source PHP Content Management System for people who want a
professional website that is easy to maintain. Visit the [project website][1]
for more information.


## About
Porting EFG to Contao 4
Based on EFG Contao 3 

 * @package   Efg
 * @author    Thomas Kuhn <mail@th-kuhn.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Thomas Kuhn 2007-2014
 * 
 * @author    Peter Broghammer <pb-contao@gmx.de>
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 * @copyright Peter Broghammer 2021-
 * 
 * Thomas Kuhn's Efg package has been completely converted to contao 4.9 

 * extended Debug
 *
 * extended by insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
 * supplies a value from an Efg form.
 * Selection field is the efgalias field that is specified in the form
 * Parameters
 * formalias: Alias name of the form (input)
 * aliasvalue: value of the alias field should have (input)
 * column: columnname for result
 * format: optional result sprintf (format, $result) 
  
 * extended useSendto
 * use the mailadress from hiddenfield sendto
 * {{link_url::14}}?sendto=dummy@example.com
 * add receipients to then Form 
  
 * ## Die einzelnenen Erweiterungen 
   * ### Debugmodus
     Durch Setzen von EFG Debugmode wird ein Debugging erzeugt. Dies ist evtl. fuer den Entwickler interessant.  
     Die Debug Information wird in var/logs/[prod/dev]-[date]-efg_debug.log gespeichert.    

   * ### insert_tag  {{efg_insert::formalias::aliasvalue::column(::format)}}
       formalias: Alias name der form (input)  
       aliasvalue: Zeile der Tabelle mit diesem Aliaswert (input)  
       column: Wert aus der Spalte der gefundenen Zeile    
       format: optional result sprintf (format, $result)  
       Es wird der Wert aus aliasvalue der Spalte column eingesetzt.  
       Damit wird es moeglich einzele Werte aus der Tabelle zu verwalten.  

   * ### insert_tag {{form::xxx}}
       Wert aus dem Formular dabei ist xxx der Feldname aus dem Formular.  
       Der Insertag kann in den verschiedenen Antworten und Mails eingesetzt werden.  
       Eine rudimentaere Abfragemoeglichkeit der Werte ist gegeben.  
       z.B.  
       {if '{{form::anrede}}' == 'Herr'}  
       Sehr geehrter Herr  
       {{form::vorname}} {{form::name}}  
       {endif}  
       {if '{{form::anrede}}' == 'Frau'}  
       Sehr geehrte Frau  
       {{form::vorname}} {{form::name}}  
       {endif}  

   * ### Hidden Feld sendto auswerten
       Ist dieses Flag gesetzt, so wird beim Emailversenden das Hiddenfeld sendto augewertet.  
       Mit dem Package contao-inputvar-bundle kann eine Environmentvariable zusaetzliche Empfenger enthalten.  
       
       Das insertag {{link_url::*}} uebernimmt angehaengte Attribute als Inputvariable.  
       Damit koennen beim Aufruf eines Formulars zusaetzliche Empfaenger angegeben werden.   
       z.b. {{link_url::446|urlattr}}?sendto=dummy@dummy.de  
       Es ist darauf zu achten, dass vom Editor kein mailto: vor das inserttag gesetzt wird (Eingabe abbrechen).  
       
       Definition des Hiddenfields im Formular:  
       Feldtyp: verstecktes Feld  
       Feldname: sendto  
       Standardwert: {{get::sendto}}  
